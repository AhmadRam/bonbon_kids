<?php

namespace Webkul\Daftra\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Daftra\Http\Controllers\DaftraController;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;

class SyncDaftraQuantities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daftra:sync-quantities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product quantities from Daftra to Bagisto';

    /**
     * @var DaftraController
     */
    protected $daftraController;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductInventoryRepository
     */
    protected $productInventoryRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        DaftraController $daftraController,
        ProductRepository $productRepository,
        ProductInventoryRepository $productInventoryRepository
    ) {
        parent::__construct();

        $this->daftraController = $daftraController;
        $this->productRepository = $productRepository;
        $this->productInventoryRepository = $productInventoryRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting Daftra quantity sync...');

        try {
            $page = 1;
            $limit = 100;
            $updatedCount = 0;
            $notFoundCount = 0;

            // Default Inventory Source in Bagisto is usually ID 1.
            // You can also get it dynamically via Webkul\Inventory\Repositories\InventorySourceRepository
            $defaultInventorySourceId = 1;

            do {
                $this->info("Fetching page {$page} from Daftra...");
                
                // Get products from Daftra API
                $daftraProductsData = $this->daftraController->getProducts($page, $limit);

                if (empty($daftraProductsData)) {
                    break;
                }

                foreach ($daftraProductsData as $daftraItem) {
                    // Daftra returns an array of items, inside each item there is a 'Product' key
                    if (!isset($daftraItem['Product'])) {
                        continue;
                    }

                    $daftraProduct = $daftraItem['Product'];
                    $productCode = $daftraProduct['product_code'] ?? null;
                    $stockBalance = $daftraProduct['stock_balance'] ?? 0;

                    // If product_code is empty, try barcode, or skip
                    $sku = $productCode ?: ($daftraProduct['barcode'] ?? null);

                    if (empty($sku)) {
                        continue;
                    }

                    $stockBalance = max(0, (float) $stockBalance);

                    // Find product in Bagisto by SKU
                    $bagistoProduct = $this->productRepository->findOneWhere(['sku' => $sku]);

                    if ($bagistoProduct) {
                        // Update Inventory
                        // Bagisto 2.x saveInventories structure: ['inventories' => [inventory_source_id => qty]]
                        $inventoryData = [
                            'inventories' => [
                                $defaultInventorySourceId => $stockBalance
                            ]
                        ];
                        
                        $this->productInventoryRepository->saveInventories($inventoryData, $bagistoProduct);
                        
                        // Update Price
                        $price = $daftraProduct['price1'] ?? $daftraProduct['price'] ?? null;
                        if ($price !== null && (float)$price > 0) {
                            // Update the product's price attribute
                            // We need to update it through the repository
                            $this->productRepository->update([
                                'price' => (float) $price,
                            ], $bagistoProduct->id);
                        }
                        
                        $this->line("Updated SKU: {$sku} | New Qty: {$stockBalance} | New Price: {$price}");
                        $updatedCount++;
                    } else {
                        $notFoundCount++;
                    }
                }

                // If less than limit returned, we're on the last page
                if (count($daftraProductsData) < $limit) {
                    break;
                }

                $page++;

            } while (true);

            $this->info("Sync completed successfully!");
            $this->info("Updated Products: {$updatedCount}");
            $this->info("Not Found in Bagisto: {$notFoundCount}");

            return 0;

        } catch (\Exception $e) {
            $this->error('Error during sync: ' . $e->getMessage());
            return 1;
        }
    }
}
