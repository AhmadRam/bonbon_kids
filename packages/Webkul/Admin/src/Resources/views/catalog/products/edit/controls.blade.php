@php
    $inputName = $attribute->code;

    if ($attribute->value_per_locale && isset($localeCode)) {
        $inputName = $attribute->code . '[' . $localeCode . ']';

        $channel = $attribute->value_per_channel ? core()->getRequestedChannelCode() : null;

        $attrValModel = $product->attribute_values
            ->where('attribute_id', $attribute->id)
            ->where('channel', $channel)
            ->where('locale', $localeCode)
            ->first();

        $attributeValue = $attrValModel ? $attrValModel[$attribute->column_name] : $attribute->default_value;
    } else {
        $attributeValue = $product[$attribute->code];
    }
@endphp

@switch($attribute->type)
    @case('text')
        <v-field
            type="text"
            name="{{ $inputName }}"
            :rules="{{ $attribute->validations }}"
            value="{{ old($inputName) ?: $attributeValue }}"
            v-slot="{ field }"
            label="{{ $attribute->admin_name }}"
        >
            <input
                type="text"
                id="{{ $inputName }}"
                :class="[errors['{{ $inputName }}'] ? 'border border-red-600 hover:border-red-600' : '']"
                class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                name="{{ $inputName }}"
                v-bind="field"
                @if ($attribute->code == 'url_key') v-slugify @endif
                @if ($attribute->code == 'name') v-slugify-target:{{ isset($localeCode) ? 'url_key[' . $localeCode . ']' : 'url_key' }}="setValues" @endif
            >
        </v-field>

        @break
    @case('price')
        <x-admin::form.control-group.control
            type="price"
            :id="$inputName"
            :class="($attribute->code == 'price' ? 'py-2.5 bg-gray-50 text-xl font-bold' : '')"
            :name="$inputName"
            ::rules="{{ $attribute->validations }}"
            value="{{ old($inputName) ?: $attributeValue }}"
            :label="$attribute->admin_name"
        >
            <x-slot:currency :class="'dark:text-gray-300 ' . ($attribute->code == 'price' ? 'bg-gray-50 dark:bg-gray-900 text-xl' : '')">
                {{ core()->currencySymbol(core()->getBaseCurrencyCode()) }}
            </x-slot>
        </x-admin::form.control-group.control>

        @break
    @case('textarea')
        <x-admin::form.control-group.control
            type="textarea"
            :id="$inputName"
            :name="$inputName"
            ::rules="{{ $attribute->validations }}"
            value="{{ old($inputName) ?: $attributeValue }}"
            :label="$attribute->admin_name"
            :tinymce="(bool) $attribute->enable_wysiwyg"
        />

        @break
    @case('date')
        <x-admin::form.control-group.control
            type="date"
            :id="$inputName"
            :name="$inputName"
            ::rules="{{ $attribute->validations }}"
            value="{{ old($inputName) ?: $attributeValue }}"
            :label="$attribute->admin_name"
        />

        @break
    @case('datetime')
        <x-admin::form.control-group.control
            type="datetime"
            :name="$inputName"
            ::rules="{{ $attribute->validations }}"
            value="{{ old($inputName) ?: $attributeValue }}"
            :label="$attribute->admin_name"
        />

        @break
    @case('select')
        <x-admin::form.control-group.control
            type="select"
            :id="$inputName"
            :name="$inputName"
            ::rules="{{ $attribute->validations }}"
            :value="old($inputName) ?: $attributeValue"
            :label="$attribute->admin_name"
        >
            @php
                $selectedOption = old($inputName) ?: $attributeValue;

                if ($attribute->code === 'tax_category_id') {
                    $options = app('Webkul\Tax\Repositories\TaxCategoryRepository')->all();
                } else if ($attribute->code === 'rma_rule_id') {
                    $rmaRuleRepository = app('Webkul\RMA\Repositories\RMARuleRepository');

                    /**
                     * Only active RMA rules should be assignable to a product.
                     */
                    $options = $rmaRuleRepository->getActiveRules();

                    /**
                     * Safety Net: if this product already has a rule that has since been
                     * deactivated, append it to the options list so editing the product
                     * does not silently drop the existing assignment. The admin can then
                     * choose to switch to an active rule on save.
                     */
                    if (
                        $selectedOption
                        && ! $options->contains('id', $selectedOption)
                    ) {
                        $currentRule = $rmaRuleRepository->find($selectedOption);

                        if ($currentRule) {
                            $options->push($currentRule);
                        }
                    }
                } else {
                    $options = $attribute->options()->orderBy('sort_order')->get();
                }
            @endphp

            @foreach ($options as $option)
                <option
                    value="{{ $option->id }}"
                    {{ $selectedOption == $option->id ? 'selected' : '' }}
                    v-pre
                >
                    {{ $option->admin_name ?? $option->name }}
                </option>
            @endforeach
        </x-admin::form.control-group.control>

        @break
    @case('multiselect')
        @php
            $selectedOption = old($inputName) ?: (is_string($attributeValue) ? explode(',', $attributeValue) : []);
        @endphp

        <x-admin::form.control-group.control
            type="multiselect"
            :id="$inputName . '[]'"
            :name="$inputName . '[]'"
            ::rules="{{ $attribute->validations }}"
            :label="$attribute->admin_name"
        >
            @foreach ($attribute->options()->orderBy('sort_order')->get() as $option)
                <option
                    value="{{ $option->id }}"
                    {{ in_array($option->id, $selectedOption) ? 'selected' : ''}}
                    v-pre
                >
                    {{ $option->admin_name }}
                </option>
            @endforeach
        </x-admin::form.control-group.control>

        @break
    @case('checkbox')
        @php
            $selectedOption = old($inputName) ?: (is_string($attributeValue) ? explode(',', $attributeValue) : []);
        @endphp

        @foreach ($attribute->options as $option)
            <div class="mb-2 flex items-center gap-2.5 last:!mb-0">
                <x-admin::form.control-group.control
                    type="checkbox"
                    :id="$inputName . '_' . $option->id"
                    :name="$inputName . '[]'"
                    ::rules="{{ $attribute->validations }}"
                    :value="$option->id"
                    :for="$inputName . '_' . $option->id"
                    :label="$attribute->admin_name"
                    :checked="in_array($option->id, $selectedOption)"
                />

                <label
                    class="cursor-pointer select-none text-xs font-medium text-gray-600 dark:text-gray-300"
                    for="{{ $inputName . '_' . $option->id }}"
                    v-pre
                >
                    {{ $option->admin_name }}
                </label>
            </div>
        @endforeach

        @break
    @case('boolean')
        @php $selectedValue = old($inputName) ?: $attributeValue @endphp

        <x-admin::form.control-group.control
            type="switch"
            :id="$inputName"
            :name="$inputName"
            :value="1"
            :label="$attribute->admin_name"
            :checked="(boolean) $selectedValue"
        />

        @break
    @case('image')
    @case('file')
        <div class="flex gap-2.5">
            @if ($attributeValue)
                <a
                    href="{{ route('admin.catalog.products.file.download', [$product->id, $attribute->id, 'locale' => $localeCode ?? null] )}}"
                    class="flex"
                >
                    @if ($attribute->type == 'image')
                        @if (Storage::exists($attributeValue))
                            <img
                                src="{{ Storage::url($attributeValue) }}"
                                class="h-[45px] w-[45px] overflow-hidden rounded border hover:border-gray-400 dark:border-gray-800"
                            />
                        @endif
                    @else
                        <div class="inline-flex w-full max-w-max cursor-pointer appearance-none items-center justify-between gap-x-1 rounded-md border border-transparent p-1.5 text-center text-gray-600 transition-all marker:shadow hover:bg-gray-200 active:border-gray-300 dark:text-gray-300 dark:hover:bg-gray-800">
                            <i class="icon-down-stat text-2xl"></i>
                        </div>
                    @endif
                </a>

                <input
                    type="hidden"
                    name="{{ $inputName }}"
                    value="{{ $attributeValue }}"
                />
            @endif

            <v-field
                type="file"
                class="w-full"
                name="{{ $inputName }}"
                :rules="{{ $attribute->validations }}"
                v-slot="{ handleChange, handleBlur }"
                label="{{ $attribute->admin_name }}"
            >
                <input
                    type="file"
                    id="{{ $inputName }}"
                    :class="[errors['{{ $inputName }}'] ? 'border border-red-600 hover:border-red-600' : '']"
                    class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:text-gray-300 dark:file:bg-gray-800 dark:file:dark:text-white dark:hover:border-gray-400 dark:focus:border-gray-400"
                    name="{{ $inputName }}"
                    @change="handleChange"
                    @blur="handleBlur"
                >
            </v-field>
        </div>

        @if ($attributeValue)
            <div class="mt-2.5 flex items-center gap-2.5">
                <x-admin::form.control-group.control
                    type="checkbox"
                    :id="$inputName . '_delete'"
                    :name="$inputName . '[delete]'"
                    value="1"
                    :for="$inputName . '_delete'"
                />

                <label
                    for="{{ $inputName . '_delete' }}"
                    class="cursor-pointer select-none text-sm text-gray-600 dark:text-gray-300"
                >
                    @lang('admin::app.catalog.products.edit.remove')
                </label>
            </div>
        @endif

        @break
@endswitch
