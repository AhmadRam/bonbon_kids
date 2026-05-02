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

            <!-- First Name -->
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

                <!-- Last Name -->
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

            <!-- Email -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required !mt-0">
                    @lang('shop::app.checkout.onepage.address.email')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="email"
                    ::name="controlName + '.email'"
                    ::value="address.email"
                    rules="required|email"
                    :label="trans('shop::app.checkout.onepage.address.email')"
                    placeholder="email@example.com"
                />

                <x-shop::form.control-group.error ::name="controlName + '.email'" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.address.form.email.after') !!}

            <!-- Vat ID -->
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

            <div class="grid grid-cols-2 gap-x-5 max-md:grid-cols-1">
                <!-- Country -->
                <x-shop::form.control-group class="!mb-4">
                    <x-shop::form.control-group.label class="{{ core()->isCountryRequired() ? 'required' : '' }} !mt-0">
                        @lang('shop::app.checkout.onepage.address.country')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="select"
                        ::name="controlName + '.country'"
                        ::value="address.country"
                        v-model="selectedCountry"
                        rules="{{ core()->isCountryRequired() ? 'required' : '' }}"
                        :label="trans('shop::app.checkout.onepage.address.country')"
                    >
                        <option
                            v-for="country in countries"
                            :key="country.code"
                            :value="country.code"
                        >
                            @{{ country.name }}
                        </option>
                    </x-shop::form.control-group.control>

                    <x-shop::form.control-group.error ::name="controlName + '.country'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.country.after') !!}

                <!-- State -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="{{ core()->isStateRequired() ? 'required' : '' }} !mt-0">
                        @lang('shop::app.checkout.onepage.address.state')
                    </x-shop::form.control-group.label>

                    <template v-if="haveStates">
                        <x-shop::form.control-group.control
                            type="select"
                            ::key="`state-select-${selectedCountry}`"
                            ::name="controlName + '.state'"
                            v-model="address.state"
                            @change="handleStateChange"
                            rules="{{ core()->isStateRequired() ? 'required' : '' }}"
                            ::value="address.state"
                            :label="trans('shop::app.checkout.onepage.address.state')"
                        >
                            <option
                                v-for='(state, index) in currentStates'
                                :key="state.id"
                                :value="state.code"
                            >
                                @{{ state.default_name }}
                            </option>
                        </x-shop::form.control-group.control>
                    </template>

                    <template v-else>
                        <x-shop::form.control-group.control
                            type="text"
                            ::key="`state-input-${selectedCountry}`"
                            ::name="controlName + '.state'"
                            v-model="address.state"
                            ::value="address.state"
                            rules="{{ core()->isStateRequired() ? 'required' : '' }}"
                            :label="trans('shop::app.checkout.onepage.address.state')"
                            :placeholder="trans('shop::app.checkout.onepage.address.state')"
                        />
                    </template>

                    <x-shop::form.control-group.error ::name="controlName + '.state'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.state.after') !!}
            </div>

            <div class="grid grid-cols-2 gap-x-5 max-md:grid-cols-1">
                <!-- City -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('shop::app.checkout.onepage.address.city')
                    </x-shop::form.control-group.label>

                    <template v-if="haveCities">
                        <x-shop::form.control-group.control
                            type="hidden"
                            ::name="controlName + '.city'"
                            v-model="address.city"
                        />

                        <select
                            ref="citySelect"
                            :key="`city-select-${selectedStateId || 'none'}-${currentCities.length}`"
                            v-model="address.city"
                            class="custom-select mb-1.5 w-full rounded-lg border border-zinc-200 bg-white px-5 py-3 text-base text-gray-600 transition-all hover:border-gray-400 focus-visible:outline-none max-md:py-2 max-sm:px-4 max-sm:text-sm"
                        >
                            <option
                                v-for='(cityData, index) in currentCities'
                                :key="cityData.id"
                                :value="cityData.code"
                            >
                                @{{ cityData.default_name }}
                            </option>
                        </select>
                    </template>

                    <template v-else>
                        <x-shop::form.control-group.control
                            type="text"
                            ::key="`city-input-${selectedStateId || 'none'}`"
                            ::name="controlName + '.city'"
                            v-model="address.city"
                            rules="required"
                            :label="trans('shop::app.checkout.onepage.address.city')"
                            :placeholder="trans('shop::app.checkout.onepage.address.city')"
                        />
                    </template>

                    <x-shop::form.control-group.error ::name="controlName + '.city'" />
                </x-shop::form.control-group>

                {!! view_render_event('bagisto.shop.checkout.onepage.address.form.city.after') !!}
            </div>

            <!-- Street Address -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required !mt-0">
                    @lang('shop::app.checkout.onepage.address.street-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    ::name="controlName + '.address.[0]'"
                    ::value="address.address[0]"
                    rules="required|address"
                    :label="trans('shop::app.checkout.onepage.address.street-address')"
                    :placeholder="trans('shop::app.checkout.onepage.address.street-address')"
                />

                <x-shop::form.control-group.error
                    class="mb-2"
                    ::name="controlName + '.address.[0]'"
                />

                @if (core()->getConfigData('customer.address.information.street_lines') > 1)
                    @for ($i = 1; $i < core()->getConfigData('customer.address.information.street_lines'); $i++)
                        <x-shop::form.control-group.control
                            type="text"
                            ::name="controlName + '.address.[{{ $i }}]'"
                            rules="address"
                            :label="trans('shop::app.checkout.onepage.address.street-address')"
                            :placeholder="trans('shop::app.checkout.onepage.address.street-address')"
                        />

                        <x-shop::form.control-group.error
                            class="mb-2"
                            ::name="controlName + '.address.[{{ $i }}]'"
                        />
                    @endfor
                @endif
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

            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required !mt-0">
                    @lang('shop::app.checkout.onepage.address.telephone')
                </x-shop::form.control-group.label>

                <div class="flex gap-x-2">
                    <div class="w-[100px] min-w-[100px]">
                        <x-shop::form.control-group.control
                            type="select"
                            ::name="controlName + '.phone_prefix'"
                            v-model="phonePrefix"
                            class="!mb-0"
                        >
                            <option value="+965">KW (+965)</option>
                            <option value="+966">SA (+966)</option>
                            <option value="+971">AE (+971)</option>
                            <option value="+974">QA (+974)</option>
                            <option value="+973">BH (+973)</option>
                            <option value="+968">OM (+968)</option>
                        </x-shop::form.control-group.control>
                    </div>

                    <div class="flex-1">
                        <x-shop::form.control-group.control
                            type="text"
                            ::name="controlName + '.phone_number'"
                            v-model="phoneNumber"
                            rules="required|numeric"
                            :label="trans('shop::app.checkout.onepage.address.telephone')"
                            :placeholder="trans('shop::app.checkout.onepage.address.telephone')"
                        />

                        <x-shop::form.control-group.control 
                            type="hidden" 
                            ::name="controlName + '.phone'" 
                            v-model="address.phone"
                        />
                    </div>
                </div>

                <x-shop::form.control-group.error ::name="controlName + '.phone_number'" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.address.form.phone.after') !!}
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
                        address: [],
                        country: '',
                        state: '',
                        city: '',
                        postcode: '',
                        phone: '',
                    }),
                },
            },

            data() {
                return {
                    selectedCountry: this.address.country,

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
                selectedCountry(newVal) {
                    this.address.country = newVal;
                    this.syncStateSelection();
                },

                'address.state': function(newVal, oldVal) {
                    if (this.isLoadingData || newVal === oldVal) return;

                    this.syncCitySelection(true);
                },

                currentCities(newCities) {
                    if (!newCities || !newCities.length) return;

                    this.syncCitySelection(true);
                },

                phonePrefix() {
                    this.mergePhone();
                },

                phoneNumber() {
                    this.mergePhone();
                },
            },

            mounted() {
                this.initPhone();
                this.loadAllData();
            },

            methods: {
                initPhone() {
                    if (this.address.phone) {
                        const prefixes = ['+965', '+966', '+971', '+974', '+973', '+968'];
                        let found = false;

                        for (const prefix of prefixes) {
                            if (this.address.phone.startsWith(prefix)) {
                                this.phonePrefix = prefix;
                                this.phoneNumber = this.address.phone.substring(prefix.length);
                                found = true;

                                break;
                            }
                        }

                        if (!found) {
                            this.phoneNumber = this.address.phone;
                        }
                    }
                },

                mergePhone() {
                    this.address.phone = this.phonePrefix + this.phoneNumber;
                },

                handleStateChange(event) {
                    const selectedState = this.currentStates.find(state => state.code === event.target.value);

                    this.address.state = event.target.value;
                    this.activeStateId = selectedState ? selectedState.id : null;

                    this.syncCitySelection(true);
                },

                isSameState(state, value) {
                    return state.code === value || state.default_name === value;
                },

                resolveSelectedCountry() {
                    if (!this.countries.length) {
                        return '';
                    }

                    return this.countries.some(country => country.code === this.selectedCountry)
                        ? this.selectedCountry
                        : this.countries[0].code;
                },

                syncStateSelection() {
                    if (!this.currentStates.length) {
                        this.activeStateId = null;
                        this.address.state = '';
                        this.address.city = '';

                        return;
                    }

                    const matchedState = this.currentStates.find(state => this.isSameState(state, this.address.state));
                    const resolvedState = matchedState || this.currentStates[0];

                    this.address.state = resolvedState.code;
                    this.activeStateId = resolvedState.id;

                    this.syncCitySelection(true);
                },

                syncCitySelection(forceFirst = false) {
                    const targetStateId = this.selectedStateId;
                    const currentValue = this.address.city;

                    this.address.city = '';

                    this.$nextTick(() => {
                        if (!targetStateId || targetStateId !== this.selectedStateId || !this.currentCities.length) {
                            return;
                        }

                        const matchedCity = this.currentCities.find(city => city.default_name === currentValue || city.code === currentValue);
                        const nextCity = forceFirst
                            ? this.currentCities[0].code
                            : (matchedCity ? matchedCity.code : this.currentCities[0].code);

                        this.address.city = nextCity;

                        this.$nextTick(() => {
                            if (targetStateId === this.selectedStateId) {
                                this.address.city = nextCity;

                                if (this.$refs.citySelect) {
                                    this.$refs.citySelect.value = nextCity;
                                    this.$refs.citySelect.selectedIndex = 0;
                                }
                            }
                        });
                    });
                },

                loadAllData() {
                    this.isLoadingData = true;

                    this.$axios.get("{{ route('shop.api.core.countries') }}")
                        .then(response => {
                            this.countries = response.data.data;
                            this.selectedCountry = this.resolveSelectedCountry();
                            this.address.country = this.selectedCountry;

                            return this.$axios.get("{{ route('shop.api.core.states') }}");
                        })
                        .then(response => {
                            this.states = response.data.data || {};
                            this.syncStateSelection();

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

