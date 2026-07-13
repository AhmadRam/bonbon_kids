<x-admin::layouts>
    <!-- Title of the page -->
    <x-slot:title>
        @lang('admin::app.settings.inventory-sources.transfer.transfer-title')
    </x-slot>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="text-xl text-gray-800 dark:text-white font-bold">
            @lang('admin::app.settings.inventory-sources.transfer.transfer-title')
        </p>

        <div class="flex gap-x-2.5 items-center">

            <!-- Custom Mass Print Button -->
            <button onclick="customMassPrint()" class="primary-button" id="customPrintBtn" disabled>
                🖨️ @lang('admin::app.sales.invoices.view.print')
            </button>

            <!-- Export Modal -->
            <x-admin::datagrid.export src="{{ route('admin.settings.inventory_sources.index_transfer') }}" />

            <a href="{{ route('admin.settings.inventory_sources.transfer') }}">
                <div class="primary-button">
                    @lang('admin::app.settings.inventory-sources.transfer.save-btn')
                </div>
            </a>

        </div>
    </div>

    {!! view_render_event('bagisto.admin.settings.inventory_sources.index_transfer.before') !!}

    <x-admin::datagrid :src="route('admin.settings.inventory_sources.index_transfer')" />

    {!! view_render_event('bagisto.admin.settings.inventory_sources.index_transfer.after') !!}

    <style>
        #customPrintBtn {
            transition: all 0.3s ease;
        }

        #customPrintBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed !important;
        }

        #customPrintBtn:not(:disabled):hover {
            background-color: #4F46E5;
            transform: translateY(-1px);
        }
    </style>

    <script>
        // Custom function to handle mass print from button
        function customMassPrint() {
            console.log('customMassPrint called'); // Debug

            // Try multiple selectors for checkboxes
            const selectors = [
                'input[name="mass_action_value[]"]:checked',
                'input[type="checkbox"]:checked:not([name="mass_action_select_all"])',
                '.datagrid input[type="checkbox"]:checked:not(.select-all)',
                'table input[type="checkbox"]:checked'
            ];

            let checkedBoxes = null;
            for (const selector of selectors) {
                checkedBoxes = document.querySelectorAll(selector);
                console.log(`Selector "${selector}" found:`, checkedBoxes.length, 'checkboxes');
                if (checkedBoxes.length > 0) break;
            }

            if (!checkedBoxes || checkedBoxes.length === 0) {
                alert('{{ __("admin::app.datagrid.mass-actions.no-records") }}');
                return;
            }

            // Extract IDs
            const ids = Array.from(checkedBoxes).map(checkbox => {
                return checkbox.value || checkbox.getAttribute('value') || checkbox.dataset.id;
            }).filter(id => id && id !== 'on'); // Filter out empty values and 'on'

            console.log('Selected IDs:', ids); // Debug

            if (ids.length === 0) {
                alert('لا يمكن الحصول على معرفات العناصر المحددة');
                return;
            }

            // Create URL with GET parameters
            const url = '{{ route("admin.settings.inventory_sources.mass_print_transfer") }}?indices=' + ids.join(',');

            console.log('Print URL:', url); // Debug

            // Open in new window for printing
            window.open(url, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        }

        // Function to enable/disable print button based on selection
        function updatePrintButton() {
            const selectors = [
                'input[name="mass_action_value[]"]:checked',
                'input[type="checkbox"]:checked:not([name="mass_action_select_all"])',
                '.datagrid input[type="checkbox"]:checked:not(.select-all)'
            ];

            let checkedBoxes = null;
            for (const selector of selectors) {
                checkedBoxes = document.querySelectorAll(selector);
                if (checkedBoxes.length > 0) break;
            }

            const printBtn = document.getElementById('customPrintBtn');

            if (printBtn) {
                const hasSelection = checkedBoxes && checkedBoxes.length > 0;
                console.log('Updating button state. Has selection:', hasSelection); // Debug

                if (hasSelection) {
                    printBtn.disabled = false;
                    printBtn.classList.remove('opacity-50');
                    printBtn.style.cursor = 'pointer';
                    printBtn.style.backgroundColor = '#6366F1';
                } else {
                    printBtn.disabled = true;
                    printBtn.classList.add('opacity-50');
                    printBtn.style.cursor = 'not-allowed';
                    printBtn.style.backgroundColor = '#9CA3AF';
                }
            }
        }

        // Wait for DOM to load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing...'); // Debug

            // Function to monitor checkbox changes
            function monitorCheckboxes() {
                const allCheckboxes = document.querySelectorAll([
                    'input[type="checkbox"]',
                    '.datagrid input',
                    'table input'
                ].join(','));

                console.log('Found total checkboxes:', allCheckboxes.length); // Debug

                allCheckboxes.forEach(checkbox => {
                    checkbox.removeEventListener('change', updatePrintButton);
                    checkbox.removeEventListener('click', updatePrintButton);

                    checkbox.addEventListener('change', function() {
                        console.log('Checkbox changed:', this.value || this.id); // Debug
                        setTimeout(updatePrintButton, 100);
                    });

                    checkbox.addEventListener('click', function() {
                        setTimeout(updatePrintButton, 100);
                    });
                });
            }

            // Initialize everything with multiple attempts
            function initializeAll() {
                updatePrintButton();
                monitorCheckboxes();
            }

            // Try multiple times to catch dynamic content
            initializeAll();
            setTimeout(initializeAll, 500);
            setTimeout(initializeAll, 1000);
            setTimeout(initializeAll, 2000);
            setTimeout(initializeAll, 3000);

            // Monitor for dynamic changes
            const observer = new MutationObserver(function(mutations) {
                let shouldUpdate = false;
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                if (node.querySelector && (
                                    node.querySelector('input[type="checkbox"]') ||
                                    node.tagName === 'TABLE' ||
                                    node.classList.contains('datagrid')
                                )) {
                                    shouldUpdate = true;
                                }
                            }
                        });
                    }
                });

                if (shouldUpdate) {
                    console.log('DOM mutation detected, reinitializing...'); // Debug
                    setTimeout(initializeAll, 100);
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            // Also monitor for click events on the entire document
            document.addEventListener('click', function(e) {
                if (e.target && e.target.type === 'checkbox') {
                    setTimeout(updatePrintButton, 50);
                }
            });
        });
    </script>

</x-admin::layouts>
