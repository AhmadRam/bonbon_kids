<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\ResponseCache\Facades\ResponseCache;
use Webkul\Shop\Http\Controllers\SitemapController;

class CleanupUrlRewritesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bagisto:url-rewrites:cleanup
                            {--dry-run : Report invalid rewrites without deleting them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up conflicting, dead-target, duplicate, and self-looping URL rewrites';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $activeProductSlugs = $this->getActiveProductSlugs();
        $activeCategorySlugs = $this->getActiveCategorySlugs();

        $rewrites = DB::table('url_rewrites')
            ->orderBy('id')
            ->get();

        $selfLoopIds = [];
        $activeCollisionIds = [];
        $activeCollisionDetails = [];
        $deadTargetIds = [];
        $keptByKey = [];
        $duplicateIds = [];

        foreach ($rewrites as $rewrite) {
            $requestPath = trim((string) $rewrite->request_path, '/');
            $rawTarget = trim((string) $rewrite->target_path);
            $targetPath = trim($rawTarget, '/');

            if ($requestPath === '' || $targetPath === '' || $requestPath === $targetPath) {
                $selfLoopIds[] = $rewrite->id;

                continue;
            }

            if (
                in_array($rewrite->entity_type, ['product', 'category'], true)
                && (isset($activeProductSlugs[$requestPath]) || isset($activeCategorySlugs[$requestPath]))
            ) {
                $activeCollisionIds[] = $rewrite->id;
                $activeCollisionDetails[] = sprintf(
                    '  #%d [%s/%s] %s => %s',
                    $rewrite->id,
                    $rewrite->entity_type,
                    $rewrite->locale,
                    $rewrite->request_path,
                    $rewrite->target_path
                );

                continue;
            }

            if (! preg_match('#^https?://#i', $rawTarget)) {
                $isDeadProduct = $rewrite->entity_type === 'product' && ! isset($activeProductSlugs[$targetPath]);
                $isDeadCategory = $rewrite->entity_type === 'category' && ! isset($activeCategorySlugs[$targetPath]);

                if ($isDeadProduct || $isDeadCategory) {
                    $deadTargetIds[] = $rewrite->id;

                    continue;
                }
            }

            $dedupeKey = "{$rewrite->entity_type}|{$requestPath}|{$rewrite->locale}";

            if (isset($keptByKey[$dedupeKey])) {
                // Keep the newest (highest ID) entry and mark the older one for deletion.
                $duplicateIds[] = $keptByKey[$dedupeKey]->id;
            }

            $keptByKey[$dedupeKey] = $rewrite;
        }

        $idsToDelete = array_values(array_unique(array_merge(
            $selfLoopIds,
            $activeCollisionIds,
            $deadTargetIds,
            $duplicateIds
        )));

        $totalCount = $rewrites->count();
        $deleteCount = count($idsToDelete);
        $remainingCount = $totalCount - $deleteCount;

        $this->info(($dryRun ? '[DRY RUN] ' : '').'URL Rewrites Cleanup Summary:');
        $this->line("  Total rows:         {$totalCount}");
        $this->line('  Self-loops/empty:   '.count($selfLoopIds));
        $this->line('  Active collisions:  '.count($activeCollisionIds));
        $this->line('  Dead targets:       '.count($deadTargetIds));
        $this->line('  Duplicates:         '.count($duplicateIds));
        $this->line("  Total to delete:    {$deleteCount}");
        $this->line("  Remaining valid:    {$remainingCount}");

        if (! empty($activeCollisionDetails)) {
            $this->warn('Active entity collisions detected:');
            foreach ($activeCollisionDetails as $detail) {
                $this->line($detail);
            }
        }

        if ($dryRun) {
            $this->comment('Dry run complete. No database changes were made.');

            return self::SUCCESS;
        }

        foreach (array_chunk($idsToDelete, 200) as $chunk) {
            DB::table('url_rewrites')->whereIn('id', $chunk)->delete();
        }

        $flattenedCount = $this->flattenRedirectChains();

        if ($flattenedCount > 0) {
            $this->info("Flattened {$flattenedCount} chained redirect(s).");
        }

        if (class_exists(ResponseCache::class)) {
            ResponseCache::clear();
        }

        if (class_exists(SitemapController::class)) {
            SitemapController::clearCache();
        }

        $this->info('Cleanup completed and caches cleared.');

        return self::SUCCESS;
    }

    /**
     * Get a flipped lookup map of active and individually visible product url_keys.
     *
     * @return array<string, int>
     */
    protected function getActiveProductSlugs(): array
    {
        $urlKeyAttrId = DB::table('attributes')->where('code', 'url_key')->value('id');
        $statusAttrId = DB::table('attributes')->where('code', 'status')->value('id');
        $visibleAttrId = DB::table('attributes')->where('code', 'visible_individually')->value('id');

        if (! $urlKeyAttrId || ! $statusAttrId || ! $visibleAttrId) {
            return [];
        }

        $slugs = DB::table('product_attribute_values as pav_u')
            ->join('product_attribute_values as pav_s', function ($join) use ($statusAttrId) {
                $join->on('pav_s.product_id', '=', 'pav_u.product_id')
                    ->where('pav_s.attribute_id', $statusAttrId)
                    ->where('pav_s.boolean_value', 1);
            })
            ->join('product_attribute_values as pav_v', function ($join) use ($visibleAttrId) {
                $join->on('pav_v.product_id', '=', 'pav_u.product_id')
                    ->where('pav_v.attribute_id', $visibleAttrId)
                    ->where('pav_v.boolean_value', 1);
            })
            ->where('pav_u.attribute_id', $urlKeyAttrId)
            ->whereNotNull('pav_u.text_value')
            ->where('pav_u.text_value', '!=', '')
            ->pluck('pav_u.text_value')
            ->map(fn ($slug) => trim((string) $slug, '/'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return array_flip($slugs);
    }

    /**
     * Get a flipped lookup map of active category slugs.
     *
     * @return array<string, int>
     */
    protected function getActiveCategorySlugs(): array
    {
        $slugs = DB::table('category_translations as ct')
            ->join('categories as c', 'c.id', '=', 'ct.category_id')
            ->where('c.status', 1)
            ->whereNotNull('ct.slug')
            ->where('ct.slug', '!=', '')
            ->pluck('ct.slug')
            ->map(fn ($slug) => trim((string) $slug, '/'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return array_flip($slugs);
    }

    /**
     * Flatten remaining multi-hop redirect chains (A -> B -> C => A -> C) and delete cycles.
     */
    protected function flattenRedirectChains(): int
    {
        $remaining = DB::table('url_rewrites')->orderBy('id')->get();
        $lookup = [];

        foreach ($remaining as $row) {
            $req = trim((string) $row->request_path, '/');
            $lookup["{$row->entity_type}|{$req}|{$row->locale}"] = $row;
        }

        $updated = 0;
        $cycleIds = [];

        foreach ($remaining as $row) {
            $req = trim((string) $row->request_path, '/');
            $currentTarget = trim((string) $row->target_path, '/');
            $visited = [$req => true];
            $hops = 0;

            while ($hops < 10) {
                $nextKey = "{$row->entity_type}|{$currentTarget}|{$row->locale}";

                if (! isset($lookup[$nextKey])) {
                    break;
                }

                if (isset($visited[$currentTarget])) {
                    $cycleIds[] = $row->id;
                    $currentTarget = null;

                    break;
                }

                $visited[$currentTarget] = true;
                $nextRow = $lookup[$nextKey];
                $currentTarget = trim((string) $nextRow->target_path, '/');
                $hops++;
            }

            if ($currentTarget !== null && $hops > 0 && $currentTarget !== trim((string) $row->target_path, '/')) {
                DB::table('url_rewrites')
                    ->where('id', $row->id)
                    ->update(['target_path' => $currentTarget]);

                $updated++;
            }
        }

        if (! empty($cycleIds)) {
            DB::table('url_rewrites')->whereIn('id', $cycleIds)->delete();
        }

        return $updated;
    }
}
