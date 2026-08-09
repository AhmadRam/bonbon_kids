<v-add-to-cart-modal ref="addToCartModal"></v-add-to-cart-modal>

@pushOnce('scripts')
    <script type="text/x-template" id="v-add-to-cart-modal-template">
        <div>
            <transition
                tag="div"
                name="modal-overlay"
                enter-class="duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-class="duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div class="fixed inset-0 z-[100] bg-gray-500 bg-opacity-50 transition-opacity" v-show="isOpen" @click="close"></div>
            </transition>

            <transition
                tag="div"
                name="modal-content"
                enter-class="duration-300 ease-out"
                enter-from-class="translate-y-4 opacity-0 md:translate-y-0 md:scale-95"
                enter-to-class="translate-y-0 opacity-100 md:scale-100"
                leave-class="duration-200 ease-in"
                leave-from-class="translate-y-0 opacity-100 md:scale-100"
                leave-to-class="translate-y-4 opacity-0 md:translate-y-0 md:scale-95"
            >
                <div class="fixed inset-0 z-[100] transform overflow-y-auto transition pointer-events-none" v-show="isOpen">
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <div class="pointer-events-auto absolute left-1/2 top-1/2 w-full max-w-[400px] -translate-x-1/2 -translate-y-1/2 overflow-hidden rounded-xl bg-white p-6 shadow-2xl max-md:w-[90%]">
                            
                            <!-- Close Button -->
                            <button @click="close" class="absolute top-3 ltr:right-3 rtl:left-3 text-red-500 hover:text-red-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            
                            <div class="flex flex-col items-center gap-4">
                                <!-- Icon -->
                                <div class="relative flex justify-center mt-2">
                                    <div class="bg-blue-100 p-4 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-12 h-12 text-toyBlue">
                                            <path d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z" />
                                        </svg>
                                    </div>
                                    <div class="absolute -top-2 ltr:-right-2 rtl:-left-2 bg-green-500 rounded-full border-2 border-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Title and Subtitle -->
                                <div class="text-center mt-2">
                                    <h3 class="text-xl font-bold text-gray-900 mb-1">تمت الإضافة بنجاح!</h3>
                                    <p class="text-sm text-gray-500">أكمل طلبك ليصلك خلال ساعات</p>
                                </div>

                                <!-- Payment Methods -->
                                <div class="w-full text-center mt-4 mb-2">
                                    <p class="text-sm font-semibold text-toyBlue mb-3">طرق الدفع</p>
                                    <div class="flex justify-center items-center gap-5">
                                        <img src="{{ bagisto_asset('images/cash-on-delivery.png') }}" alt="Cash on Delivery" class="h-8 object-contain">
                                        <img src="{{ bagisto_asset('images/apple-pay.png') }}" alt="Apple Pay" class="h-8 object-contain" style="transform: scale(1.8); margin: 0 7px;">
                                        <img src="{{ bagisto_asset('images/cc.png') }}" alt="Visa Mastercard" class="h-8 object-contain" style="transform: scale(1.8); margin: 0 7px;">
                                        <img src="{{ bagisto_asset('images/knet.png') }}" alt="KNET" class="h-8 object-contain" style="transform: scale(1.8); margin: 0 7px;">
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="w-full flex flex-col gap-3 mt-4">
                                    <a href="{{ route('shop.checkout.onepage.index') }}" class="w-full text-center bg-toyBlue text-white font-bold py-3 px-4 rounded-xl hover:bg-opacity-90 transition-all">
                                        متابعة الطلب
                                    </a>
                                    <button type="button" @click="close" class="w-full bg-white text-toyBlue border-2 border-toyBlue font-bold py-3 px-4 rounded-xl hover:bg-gray-50 transition-all">
                                        متابعة التسوق
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </script>

    <script type="module">
        app.component('v-add-to-cart-modal', {
            template: '#v-add-to-cart-modal-template',

            data() {
                return {
                    isOpen: false,
                };
            },

            created() {
                this.$emitter.on('open-add-to-cart-modal', this.open);
            },

            methods: {
                open() {
                    this.isOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                close() {
                    this.isOpen = false;
                    document.body.style.overflow = 'auto';
                }
            }
        });
    </script>
@endPushOnce
