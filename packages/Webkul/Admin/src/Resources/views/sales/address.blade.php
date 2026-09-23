<div class="flex flex-col">
    <p 
        class="font-semibold leading-6 text-gray-800 dark:text-white"
        v-text="'{{ $address->company_name ?? '' }}'"
    >
    </p>

    <p 
        class="font-semibold leading-6 text-gray-800 dark:text-white"
        v-text="'{{ $address->name }}'"
    >
    </p>

    @if ($address->vat_id)
        <p 
            class="font-semibold leading-6 text-gray-800 dark:text-white"
            v-text="'{{ $address->vat_id }}'"
        >
        </p>
    @endif

    <p 
        class="!leading-6 text-gray-600 dark:text-gray-300"
        v-pre
    >
        @if ($address->address)
            {{ $address->address }}<br>
        @endif

        @if ($address->city)
            {{ $address->city }}<br>
        @endif

        @if ($address->state)
            {{ $address->state }}<br>
        @endif

        {{ core()->country_name($address->country) }} @if ($address->postcode && $address->postcode !== '00000') ({{ $address->postcode }}) @endif<br>

        {{ trans('admin::app.sales.orders.view.contact') }} : {{ $address->phone }}
    </p>
</div>