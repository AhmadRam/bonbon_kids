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
            ['ar' => 'ألعاب أولاد', 'en' => 'Boys Toys', 'file' => 'boys.png'],
            ['ar' => 'ألعاب ذكية', 'en' => 'Smart Toys', 'file' => 'smart.png'],
            ['ar' => 'ألعاب تعليمية', 'en' => 'Educational Toys', 'file' => 'educational.png'],
            ['ar' => 'ألعاب مواليد', 'en' => 'Toddlers Toys', 'file' => 'toddlers.png'],
            ['ar' => 'اقل من 1 دينار', 'en' => 'Under 1 Dinar', 'file' => 'under-1-dinar.png'],
            ['ar' => 'ألعاب بنات', 'en' => 'Girls Toys', 'file' => 'girls.png'],
            ['ar' => 'ألعاب خارجية', 'en' => 'Outdoor Toys', 'file' => 'outdoor.png'],
            ['ar' => 'عروض', 'en' => 'Offers', 'file' => 'offers.png'],
        ];

        $groupAttr = $this->attributeRepository->findOneByField('code', 'group');
        if (! $groupAttr) {
            if (isset($this->command)) {
                $this->command->error('Group attribute not found!');
            }

            return;
        }

        // Delete existing options of the group attribute to start fresh
        $options = $this->attributeOptionRepository->findByField('attribute_id', $groupAttr->id);
        foreach ($options as $option) {
            try {
                $this->attributeOptionRepository->delete($option->id);
            } catch (\Exception $e) {
                // Ignore exception
            }
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
