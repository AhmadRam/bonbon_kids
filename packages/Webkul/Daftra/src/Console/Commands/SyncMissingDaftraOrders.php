<?php

namespace Webkul\Daftra\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Daftra\Listeners\OrderListener;
use Webkul\Sales\Models\Order;

class SyncMissingDaftraOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daftra:sync-missing-orders
        {--order-id=* : Specific order IDs to sync}
        {--since=7 : Days back to check (default 7)}
        {--dry-run : List unsynced orders without syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Bagisto orders that do not have a Daftra invoice yet';

    /**
     * Execute the console command.
     */
    public function handle(OrderListener $orderListener): int
    {
        if (empty(config('daftra.api_key')) && empty(config('daftra.client_secret'))) {
            $this->error('Daftra credentials are not configured.');

            return 1;
        }

        $orderIds = array_values(array_filter(array_map('intval', (array) $this->option('order-id'))));
        $sinceDays = max(1, (int) $this->option('since'));
        $dryRun = (bool) $this->option('dry-run');

        $query = Order::with(['items.child', 'payment', 'addresses'])
            ->whereNull('daftra_invoice_id')
            ->whereNotIn('status', ['canceled', 'closed'])
            ->orderBy('id');

        if (! empty($orderIds)) {
            $query->whereIn('id', $orderIds);
        } else {
            $query->where('created_at', '>=', now()->subDays($sinceDays));
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->info('No unsynced orders found.');

            return 0;
        }

        if ($dryRun) {
            $rows = $orders->map(fn ($order) => [
                $order->id,
                $order->increment_id,
                $order->status,
                $order->payment->method ?? 'n/a',
                $order->grand_total,
                (string) $order->created_at,
            ])->all();

            $this->table(['ID', 'Increment ID', 'Status', 'Payment', 'Grand Total', 'Created At'], $rows);

            return 0;
        }

        $synced = 0;
        $failed = 0;

        foreach ($orders as $order) {
            $this->line("Syncing Order #{$order->id} ({$order->status}, ".($order->payment->method ?? 'unknown').')...');

            $orderListener->syncOrderToDaftra($order);
            $order->refresh();

            if (! empty($order->daftra_invoice_id)) {
                $this->info("  -> Synced to Daftra Invoice #{$order->daftra_invoice_id}");
                $synced++;
            } else {
                $this->error("  -> Failed to sync Order #{$order->id} (check logs for details)");
                $failed++;
            }
        }

        $this->info("Finished. Synced: {$synced} | Failed: {$failed}");

        return $failed > 0 ? 1 : 0;
    }
}
