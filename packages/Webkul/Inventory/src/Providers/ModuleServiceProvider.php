<?php

namespace Webkul\Inventory\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Inventory\Models\InventorySource;
use Webkul\Inventory\Models\InventoryTransfer;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        InventorySource::class,
        InventoryTransfer::class,
    ];
}
