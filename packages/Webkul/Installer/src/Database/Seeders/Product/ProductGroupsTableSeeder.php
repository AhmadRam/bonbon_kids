<?php

namespace Webkul\Installer\Database\Seeders\Product;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeOptionRepository;

class ProductGroupsTableSeeder extends Seeder
{
    protected $attributeRepository;
    protected $attributeOptionRepository;

    public function __construct(
        AttributeRepository $attributeRepository,
        AttributeOptionRepository $attributeOptionRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    public function run()
    {
        $groups = [
            ['ar' => 'العاب', 'en' => 'Toys', 'file' => 'toys.png'],
            ['ar' => 'وصل حديثا', 'en' => 'New Arrivals', 'file' => 'new-arrivals.png'],
            ['ar' => 'تعليمية', 'en' => 'Educational', 'file' => 'educational.png'],
            ['ar' => 'هدايا', 'en' => 'Gifts', 'file' => 'gifts.png'],
            ['ar' => 'اقل من 1 دينار', 'en' => 'Under 1 Dinar', 'file' => 'under-1-dinar.png'],
            ['ar' => 'ترفيه', 'en' => 'Entertainment', 'file' => 'entertainment.png'],
            ['ar' => 'رياضة', 'en' => 'Sports', 'file' => 'sports.png'],
            ['ar' => 'عروض', 'en' => 'Offers', 'file' => 'offers.png'],
        ];

        $groupAttr = $this->attributeRepository->findOneByField('code', 'group');
        if (! $groupAttr) {
            if (isset($this->command)) {
                $this->command->error('Group attribute not found!');
            }

            return;
        }

        $now = Carbon::now();

        foreach ($groups as $index => $b) {
            $existingTranslation = DB::table('attribute_option_translations')
                ->where('label', $b['en'])
                ->first();

            $optionId = null;
            if ($existingTranslation) {
                $optionId = $existingTranslation->attribute_option_id;
            } else {
                $option = $this->attributeOptionRepository->create([
                    'attribute_id' => $groupAttr->id,
                    'admin_name'   => $b['en'],
                    'sort_order'   => 1,
                ]);
                $optionId = $option->id;

                DB::table('attribute_option_translations')->updateOrInsert(
                    ['attribute_option_id' => $optionId, 'locale' => 'en'],
                    ['label' => $b['en']]
                );

                DB::table('attribute_option_translations')->updateOrInsert(
                    ['attribute_option_id' => $optionId, 'locale' => 'ar'],
                    ['label' => $b['ar']]
                );
            }

            // Also attach/update swatch images if any
            $imageFile = __DIR__ . '/Data/group-images/' . $b['file'];
            if (file_exists($imageFile)) {
                $storedPath = 'storage/' . Storage::putFile('attribute_option', new File($imageFile));
                DB::table('attribute_options')
                    ->where('id', $optionId)
                    ->update(['swatch_value' => $storedPath]);
            }
        }
    }
}
