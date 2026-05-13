<!-- Checkout Login Vue JS Component -->
<v-checkout-login>
    <div class="flex items-center">
        <span class="cursor-pointer text-base font-medium text-blue-700">
            @lang('shop::app.checkout.login.title')
        </span>
    </div>
</v-checkout-login>

@pushOnce('scripts')
    {!! \Webkul\Customer\Facades\Captcha::renderJS() !!}

    <script
        type="text/x-template"
        id="v-checkout-login-template"
    >
        <div>
            <div class="flex items-center">
                <span
                    class="cursor-pointer text-base font-medium text-blue-700"
                    role="button"
                    @click="$refs.loginModel.open()"
                >
                    @lang('shop::app.checkout.login.title')
                </span>
            </div>

            <!-- Login Form -->
            <x-shop::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                {!! view_render_event('bagisto.shop.checkout.login.before') !!}

                <!-- Login form -->
                <form @submit="handleSubmit($event, login)">
                    {!! view_render_event('bagisto.shop.checkout.login.form_controls.before') !!}

                    <!-- Login modal -->
                    <x-shop::modal ref="loginModel">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <h2 class="text-2xl font-medium max-md:text-base">
                                <template v-if="isRegister">
                                    @lang('shop::app.customers.signup-form.page-title')
                                </template>

                                <template v-else>
                                    @lang('shop::app.checkout.login.title')
                                </template>
                            </h2>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                            <div class="grid gap-y-3">
                                <template v-if="isRegister">
                                    <div class="flex gap-4">
                                        <!-- First Name -->
                                        <x-shop::form.control-group class="flex-1">
                                            <x-shop::form.control-group.label class="required">
                                                @lang('shop::app.customers.signup-form.first-name')
                                            </x-shop::form.control-group.label>

                                            <x-shop::form.control-group.control
                                                type="text"
                                                class="px-6 py-4"
                                                name="first_name"
                                                rules="required"
                                                :label="trans('shop::app.customers.signup-form.first-name')"
                                                :placeholder="trans('shop::app.customers.signup-form.first-name')"
                                            />

                                            <x-shop::form.control-group.error control-name="first_name" />
                                        </x-shop::form.control-group>

                                        <!-- Last Name -->
                                        <x-shop::form.control-group class="flex-1">
                                            <x-shop::form.control-group.label class="required">
                                                @lang('shop::app.customers.signup-form.last-name')
                                            </x-shop::form.control-group.label>

                                            <x-shop::form.control-group.control
                                                type="text"
                                                class="px-6 py-4"
                                                name="last_name"
                                                rules="required"
                                                :label="trans('shop::app.customers.signup-form.last-name')"
                                                :placeholder="trans('shop::app.customers.signup-form.last-name')"
                                            />

                                            <x-shop::form.control-group.error control-name="last_name" />
                                        </x-shop::form.control-group>
                                    </div>
                                </template>

                                <!-- Email -->
                                <x-shop::form.control-group>
                                    <x-shop::form.control-group.label class="required">
                                        @lang('shop::app.checkout.login.email')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="email"
                                        class="px-6 py-4"
                                        name="email"
                                        rules="required|email"
                                        :label="trans('shop::app.checkout.login.email')"
                                        placeholder="email@example.com"
                                        :aria-label="trans('shop::app.checkout.login.email')"
                                        aria-required="true"
                                    />

                                    <x-shop::form.control-group.error control-name="email" />
                                </x-shop::form.control-group>

                                <!-- Password -->
                                <x-shop::form.control-group :class="isRegister ? '' : '!mb-0'">
                                    <x-shop::form.control-group.label class="required">
                                        @lang('shop::app.checkout.login.password')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="password"
                                        class="px-6 py-4"
                                        id="password"
                                        name="password"
                                        rules="required|min:6"
                                        :label="trans('shop::app.checkout.login.password')"
                                        :placeholder="trans('shop::app.checkout.login.password')"
                                        :aria-label="trans('shop::app.checkout.login.password')"
                                        aria-required="true"
                                    />

                                    <x-shop::form.control-group.error control-name="password" />
                                </x-shop::form.control-group>

                                <template v-if="isRegister">
                                    <!-- Confirm Password -->
                                    <x-shop::form.control-group class="!mb-0">
                                        <x-shop::form.control-group.label class="required">
                                            @lang('shop::app.customers.signup-form.confirm-pass')
                                        </x-shop::form.control-group.label>

                                        <x-shop::form.control-group.control
                                            type="password"
                                            class="px-6 py-4"
                                            name="password_confirmation"
                                            rules="required|confirmed:@password"
                                            :label="trans('shop::app.customers.signup-form.confirm-pass')"
                                            :placeholder="trans('shop::app.customers.signup-form.confirm-pass')"
                                        />

                                        <x-shop::form.control-group.error control-name="password_confirmation" />
                                    </x-shop::form.control-group>

                                    <!-- Newsletter Subscription -->
                                    <x-shop::form.control-group class="flex items-center gap-2 !mb-0">
                                        <x-shop::form.control-group.control
                                            type="checkbox"
                                            name="is_subscribed"
                                            id="is_subscribed"
                                            value="1"
                                        />

                                        <label
                                            class="cursor-pointer select-none text-sm text-gray-600"
                                            for="is_subscribed"
                                        >
                                            @lang('shop::app.customers.signup-form.subscribe-to-newsletter')
                                        </label>
                                    </x-shop::form.control-group>
                                </template>

                                <!-- Captcha -->
                                @if (core()->getConfigData('customer.captcha.credentials.status'))
                                    <x-shop::form.control-group class="mt-5">
                                        {!! \Webkul\Customer\Facades\Captcha::render() !!}

                                        <x-shop::form.control-group.error control-name="recaptcha_token" />
                                    </x-shop::form.control-group>
                                @endif
                            </div>
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <div class="flex flex-col gap-4 w-full">
                                <div class="flex flex-wrap items-center gap-4">
                                    <x-shop::button
                                        class="primary-button max-w-none flex-auto rounded-sm px-11 py-3 max-md:rounded-lg max-md:py-1.5"
                                        ::title="isRegister ? '{{ trans('shop::app.customers.signup-form.button-title') }}' : '{{ trans('shop::app.checkout.login.title') }}'"
                                        ::loading="isStoring"
                                        ::disabled="isStoring"
                                    />
                                </div>

                                <div class="flex justify-center">
                                    <span 
                                        class="cursor-pointer text-blue-700 font-medium"
                                        @click="isRegister = ! isRegister"
                                    >
                                        <template v-if="isRegister">
                                            @lang('shop::app.customers.signup-form.account-exists') 
                                            <span class="underline">@lang('shop::app.customers.signup-form.sign-in-button')</span>
                                        </template>

                                        <template v-else>
                                            @lang('shop::app.customers.login-form.new-customer') 
                                            <span class="underline">@lang('shop::app.customers.login-form.create-your-account')</span>
                                        </template>
                                    </span>
                                </div>
                            </div>
                        </x-slot>
                    </x-shop::modal>

                    {!! view_render_event('bagisto.shop.checkout.login.form_controls.after') !!}
                </form>
            </x-shop::form>

            {!! view_render_event('bagisto.shop.checkout.login.after') !!}
        </div>
    </script>

    <script type="module">
        app.component('v-checkout-login', {
            template: '#v-checkout-login-template',

            data() {
                return {
                    isRegister: false,

                    isStoring: false,
                }
            },

            methods: {
                login(params, {
                    resetForm,
                    setErrors
                }) {
                    this.isStoring = true;

                    const captchaResponse = document.querySelector('[name="recaptcha_token"]')?.value

                    params['recaptcha_token'] = captchaResponse;

                    const url = this.isRegister 
                        ? "{{ route('shop.api.customers.store') }}" 
                        : "{{ route('shop.api.customers.session.create') }}";

                    this.$axios.post(url, params)
                        .then((response) => {
                            this.isStoring = false;

                            window.location.reload();
                        })
                        .catch((error) => {
                            this.isStoring = false;

                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);

                                return;
                            }

                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response.data.message
                            });
                        });
                },
            }
        })
    </script>
@endPushOnce
