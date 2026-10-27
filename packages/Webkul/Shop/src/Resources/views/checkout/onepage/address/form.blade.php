@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-checkout-address-form-template"
    >
        <div class="mt-2 max-md:mt-3">
            <x-shop::form.control-group class="hidden">
                <x-shop::form.control-group.control
                    type="text"
                    ::name="controlName + '.id'"
                    ::value="address.id"
                />
            </x-shop::form.control-group>

            <!-- Company Name (Hidden) -->
            <x-shop::form.control-group class="hidden">
                <x-shop::form.control-group.label>
                    @lang('shop::app.checkout.onepage.address.company-name')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    ::name="controlName + '.company_name'"
                    ::value="address.company_name"
                    :placeholder="trans('shop::app.checkout.onepage.address.company-name')"
                />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.address.form.company_name.after') !!}

            <!-- First Name & Last Name -->
            <div class="grid grid-cols-2 gap-x-5 max-md:grid-cols-1">
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('shop::app.checkout.onepage.address.first-name')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        ::name="controlName + '.first_name'"
                        ::value="address.first_name"
                        rules="required"
                        :label="trans('shop::app.checkout.onepage.address.first-name')"
                        :placeholder="trans('shop::app.checkout.onepage.address.first-name')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.first_name'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.first_name.after') !!}

                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('shop::app.checkout.onepage.address.last-name')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        ::name="controlName + '.last_name'"
                        ::value="address.last_name"
                        rules="required"
                        :label="trans('shop::app.checkout.onepage.address.last-name')"
                        :placeholder="trans('shop::app.checkout.onepage.address.last-name')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.last_name'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.last_name.after') !!}
            </div>

            <!-- Email & Phone -->
            <div class="grid grid-cols-2 gap-x-5 max-md:grid-cols-1">
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="!mt-0">
                        @lang('shop::app.checkout.onepage.address.email')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="email"
                        ::name="controlName + '.email'"
                        ::value="address.email"
                        rules="email"
                        :label="trans('shop::app.checkout.onepage.address.email')"
                        placeholder="email@example.com"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.email'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.email.after') !!}

                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('shop::app.checkout.onepage.address.telephone')
                    </x-shop::form.control-group.label>

                    <div class="flex" style="direction: ltr !important;">
                        <span
                            class="inline-flex items-center justify-center bg-zinc-100 text-zinc-700 font-medium text-base px-4 border border-zinc-200 select-none min-w-[75px] max-md:text-sm max-md:px-3 max-md:min-w-[65px]"
                            style="border-top-left-radius: 0.5rem; border-bottom-left-radius: 0.5rem; border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: 0;"
                        >
                            +965
                        </span>

                        <div class="flex-1">
                            <x-shop::form.control-group.control
                                type="text"
                                ::name="controlName + '.phone_number'"
                                v-model="phoneNumber"
                                rules="required|numeric"
                                :label="trans('shop::app.checkout.onepage.address.telephone')"
                                :placeholder="trans('shop::app.checkout.onepage.address.telephone')"
                                class="!text-left !mb-0"
                                style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;"
                            />

                            <x-shop::form.control-group.control 
                                type="hidden" 
                                ::name="controlName + '.phone'" 
                                v-model="address.phone"
                            />
                        </div>
                    </div>

                    <x-shop::form.control-group.error ::name="controlName + '.phone_number'" class="mt-1" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.phone.after') !!}
            </div>

            <!-- State & City (Governorate & Area) -->
            <div class="grid grid-cols-2 gap-x-5 max-md:grid-cols-1">
                <!-- State (Governorate) -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('shop::app.checkout.onepage.address.governorate')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="select"
                        ::key="`state-select-${selectedCountry}`"
                        ::name="controlName + '.state'"
                        v-model="address.state"
                        @change="handleStateChange"
                        rules="required"
                        ::value="address.state"
                        :label="trans('shop::app.checkout.onepage.address.governorate')"
                    >
                        <option value="">
                            @lang('shop::app.checkout.onepage.address.select-state')
                        </option>

                        <option
                            v-for="state in currentStates"
                            :key="state.id"
                            :value="state.code"
                        >
                            @{{ state.default_name }}
                        </option>
                    </x-shop::form.control-group.control>

                    <x-shop::form.control-group.error ::name="controlName + '.state'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.state.after') !!}

                <!-- City (Area) -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('shop::app.checkout.onepage.address.area')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="select"
                        ::key="`city-select-${selectedStateId || 'none'}`"
                        ::name="controlName + '.city'"
                        v-model="address.city"
                        rules="required"
                        ::value="address.city"
                        :label="trans('shop::app.checkout.onepage.address.area')"
                    >
                        <option value="">
                            @lang('shop::app.checkout.onepage.address.select-city')
                        </option>

                        <option
                            v-for="cityData in currentCities"
                            :key="cityData.id"
                            :value="cityData.default_name"
                        >
                            @{{ cityData.default_name }}
                        </option>
                    </x-shop::form.control-group.control>

                    <x-shop::form.control-group.error ::name="controlName + '.city'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.city.after') !!}
            </div>

            <!-- Full Address (Optional) -->
            <x-shop::form.control-group class="mt-2">
                <x-shop::form.control-group.label class="!mt-0">
                    @lang('shop::app.checkout.onepage.address.full-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    ::name="controlName + '.address.[0]'"
                    ::value="fullAddressValue"
                    :placeholder="trans('shop::app.checkout.onepage.address.full-address-placeholder')"
                />

                <x-shop::form.control-group.error ::name="controlName + '.address.[0]'" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.address.form.address.after') !!}

            <!-- Vat ID (Hidden) -->
            <template v-if="controlName=='billing'">
                <x-shop::form.control-group class="hidden">
                    <x-shop::form.control-group.label>
                        @lang('shop::app.checkout.onepage.address.vat-id')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        ::name="controlName + '.vat_id'"
                        ::value="address.vat_id"
                        :label="trans('shop::app.checkout.onepage.address.vat-id')"
                        :placeholder="trans('shop::app.checkout.onepage.address.vat-id')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.vat_id'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.vat_id.after') !!}
            </template>

            <!-- Country (Hidden) -->
            <x-shop::form.control-group class="hidden">
                <x-shop::form.control-group.control
                    type="text"
                    ::name="controlName + '.country'"
                    ::value="address.country || 'KW'"
                    v-model="selectedCountry"
                />
            </x-shop::form.control-group>


            <!-- Notes (Hidden) -->
            <x-shop::form.control-group class="mt-2 hidden">
                <x-shop::form.control-group.label class="!mt-0">
                    @lang('shop::app.checkout.onepage.address.notes')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    ::name="controlName + '.notes'"
                    ::value="address.notes"
                    :label="trans('shop::app.checkout.onepage.address.notes')"
                    :placeholder="trans('shop::app.checkout.onepage.address.notes')"
                />

                <x-shop::form.control-group.error
                    class="mb-2"
                    ::name="controlName + '.notes'"
                />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.address.form.address.after') !!}

            <div class="grid grid-cols-2 gap-x-5 max-md:grid-cols-1">

                <!-- Postcode (Hidden) -->
                <x-shop::form.control-group class="hidden">
                    <x-shop::form.control-group.label class="{{ core()->isPostCodeRequired() ? 'required' : '' }} !mt-0">
                        @lang('shop::app.checkout.onepage.address.postcode')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        ::name="controlName + '.postcode'"
                        ::value="address.postcode || '00000'"
                        rules="{{ core()->isPostCodeRequired() ? 'required' : '' }}|postcode"
                        :label="trans('shop::app.checkout.onepage.address.postcode')"
                        :placeholder="trans('shop::app.checkout.onepage.address.postcode')"
                    />

                    <x-shop::form.control-group.error ::name="controlName + '.postcode'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.postcode.after') !!}
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-checkout-address-form', {
            template: '#v-checkout-address-form-template',

            props: {
                controlName: {
                    type: String,
                    required: true,
                },

                address: {
                    type: Object,

                    default: () => ({
                        id: 0,
                        company_name: '',
                        first_name: '',
                        last_name: '',
                        email: '',
                        address: [''],
                        country: 'KW',
                        state: '',
                        city: '',
                        postcode: '00000',
                        phone: '',
                        notes: '',
                    }),
                },
            },

            data() {
                return {
                    selectedCountry: this.address?.country || 'KW',

                    countries: [],

                    states: {},

                    cities: {},

                    phonePrefix: '+965',

                    phoneNumber: '',

                    dataReady: false,

                    isLoadingData: true,

                    activeStateId: null,
                }
            },

            computed: {
                fullAddressValue() {
                    if (!this.address || !this.address.address) return '';

                    if (Array.isArray(this.address.address)) {
                        if (this.address.address[0] === '0' && this.address.address[1] === 'NA') {
                            return '';
                        }

                        return this.address.address.filter(Boolean).join(', ');
                    }

                    return this.address.address;
                },

                currentStates() {
                    if (!this.selectedCountry || !this.states[this.selectedCountry]) return [];

                    return this.states[this.selectedCountry];
                },

                haveStates() {
                    return this.currentStates.length > 0;
                },

                selectedState() {
                    if (!this.haveStates || !this.address.state) return null;

                    return this.currentStates.find(state => this.isSameState(state, this.address.state)) || null;
                },

                selectedStateId() {
                    return this.activeStateId ?? (this.selectedState ? this.selectedState.id : null);
                },

                currentCities() {
                    if (!this.selectedStateId || !this.cities[this.selectedStateId]) return [];

                    return this.cities[this.selectedStateId];
                },

                haveCities() {
                    return this.currentCities.length > 0;
                },
            },

            watch: {
                phoneNumber(newVal) {
                    if (newVal) {
                        let val = String(newVal).trim();

                        if (val.startsWith('+965')) {
                            this.phoneNumber = val.substring(4);
                            return;
                        } else if (val.startsWith('00965')) {
                            this.phoneNumber = val.substring(5);
                            return;
                        } else if (val.startsWith('965') && val.length > 8) {
                            this.phoneNumber = val.substring(3);
                            return;
                        }
                    }

                    this.mergePhone();
                },

                'address.phone'() {
                    this.initPhone();
                },

                'address.state'(newVal) {
                    if (!newVal) {
                        this.activeStateId = null;
                    } else {
                        const matchedState = this.currentStates.find(state => this.isSameState(state, newVal));
                        this.activeStateId = matchedState ? matchedState.id : null;
                    }
                },
            },

            mounted() {
                this.initPhone();
                this.loadAllData();
            },

            methods: {
                initPhone() {
                    this.phonePrefix = '+965';

                    if (this.address && this.address.phone) {
                        let phone = String(this.address.phone).trim();

                        if (phone.startsWith('+965')) {
                            phone = phone.substring(4);
                        } else if (phone.startsWith('00965')) {
                            phone = phone.substring(5);
                        } else if (phone.startsWith('965')) {
                            phone = phone.substring(3);
                        }

                        this.phoneNumber = phone;
                    }

                    this.mergePhone();
                },

                mergePhone() {
                    if (this.phoneNumber) {
                        const cleaned = String(this.phoneNumber).replace(/^(\+965|00965|965)/, '');
                        this.address.phone = '+965' + cleaned;
                    } else {
                        this.address.phone = '';
                    }
                },

                handleStateChange(event) {
                    const selectedCode = event.target.value;
                    const selectedState = this.currentStates.find(state => state.code === selectedCode);

                    this.address.state = selectedCode;
                    this.activeStateId = selectedState ? selectedState.id : null;
                    this.address.city = '';
                },

                isSameState(state, value) {
                    if (!value || !state) return false;
                    return state.code === value || state.default_name === value || state.id == value;
                },

                isSameCity(city, value) {
                    if (!value || !city) return false;
                    return city.default_name === value || city.code === value || city.id == value;
                },

                syncStateSelection() {
                    if (!this.currentStates.length) {
                        this.activeStateId = null;
                        return;
                    }

                    if (this.address.state) {
                        const matchedState = this.currentStates.find(state => this.isSameState(state, this.address.state));

                        if (matchedState) {
                            this.address.state = matchedState.code;
                            this.activeStateId = matchedState.id;
                            this.syncCitySelection();
                            return;
                        }
                    }

                    this.activeStateId = null;
                    this.address.state = '';
                    this.address.city = '';
                },

                syncCitySelection() {
                    const targetStateId = this.selectedStateId;
                    const currentValue = this.address.city;

                    if (!targetStateId || !this.currentCities.length) {
                        this.address.city = '';
                        return;
                    }

                    if (currentValue) {
                        const matchedCity = this.currentCities.find(city => this.isSameCity(city, currentValue));

                        if (matchedCity) {
                            this.address.city = matchedCity.default_name;
                            return;
                        }
                    }

                    this.address.city = '';
                },

                loadAllData() {
                    this.isLoadingData = true;

                    this.$axios.get("{{ route('shop.api.core.countries') }}")
                        .then(response => {
                            this.countries = response.data.data;
                            this.selectedCountry = 'KW';
                            this.address.country = 'KW';

                            return this.$axios.get("{{ route('shop.api.core.states') }}");
                        })
                        .then(response => {
                            this.states = response.data.data || {};

                            return this.$axios.get("{{ route('shop.api.core.cities') }}");
                        })
                        .then(response => {
                            this.cities = response.data.data || {};
                            this.syncStateSelection();
                            this.isLoadingData = false;
                            this.dataReady = true;
                        })
                        .catch(() => {
                            this.isLoadingData = false;
                            this.dataReady = true;
                        });
                },
            }
        });
    </script>
@endPushOnce
