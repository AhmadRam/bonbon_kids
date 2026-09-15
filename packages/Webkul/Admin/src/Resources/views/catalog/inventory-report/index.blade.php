<x-admin::layouts>
    <x-slot:title>
        Inventory Report
    </x-slot:title>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Inventory Report (Default vs Shop)
        </p>

        <div class="flex items-center gap-x-2.5">
            <!-- Export Modal -->
            <x-admin::datagrid.export :src="route('admin.catalog.inventory_report.index')" />
        </div>
    </div>

    <!-- Datagrid -->
    <x-admin::datagrid :src="route('admin.catalog.inventory_report.index')"></x-admin::datagrid>
</x-admin::layouts>
