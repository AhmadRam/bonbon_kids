<?php

namespace Webkul\Admin\Http\Controllers\Catalog;

use Illuminate\Support\Facades\Event;
use Webkul\Admin\DataGrids\Catalog\InventoryReportDataGrid;
use Webkul\Admin\Http\Controllers\Controller;

class InventoryReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(InventoryReportDataGrid::class)->toJson();
        }

        return view('admin::catalog.inventory-report.index');
    }
}
