<v-image-carousel :errors="errors">
    <x-admin::shimmer.settings.themes.image-carousel />
</v-image-carousel>

<!-- Image Carousel Vue Component -->
@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-image-carousel-template"
    >
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.slider')
                        </p>
                        
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.slider-description')
                        </p>
                    </div>

                    <!-- Add Slider Button -->
                    <div
                        class="secondary-button"
                        @click="create"
                    >
                        @lang('admin::app.settings.themes.edit.slider-add-btn')
                    </div>
                </div>

                <template v-for="(deletedSlider, index) in deletedSliders">
                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[deleted_sliders]['+ index +'][image]'"
                        :value="deletedSlider.image"
                    />
                </template>

                <template v-if="sliders.images.length">
                    <div
                        class="grid pt-4"
                        v-for="(image, index) in sliders.images"
                        :key="image._uid"
                    >
                        <!-- Hidden Input -->
                        <input
                            type="file"
                            class="hidden"
                            :name="'{{ $currentLocale->code }}[options]['+ index +'][image]'"
                            :ref="'imageInput_' + image._uid"
                        />

                        <input
                            type="hidden"
                            :name="'{{ $currentLocale->code }}[options]['+ index +'][title]'"
                            :value="image.title"
                        />

                        <input
                            type="hidden"
                            :name="'{{ $currentLocale->code }}[options]['+ index +'][link]'"
                            :value="image.link"
                        />

                        <input
                            type="hidden"
                            :name="'{{ $currentLocale->code }}[options]['+ index +'][image]'"
                            :value="image.image"
                        />
                    
                        <!-- Details -->
                        <div 
                            class="flex cursor-pointer justify-between gap-2.5 py-5"
                            :class="{
                                'border-b border-slate-300 dark:border-gray-800': index < sliders.images.length - 1
                            }"
                        >
                            <div class="flex items-start gap-4">
                                <img
                                    v-if="image._previewUrl || image.image"
                                    :src="image._previewUrl || ('{{ rtrim(asset('/'), '/') }}/' + image.image)"
                                    :ref="'imagePreview_' + image._uid"
                                    alt="@lang('admin::app.settings.themes.edit.slider-image')"
                                    class="h-20 w-36 shrink-0 rounded border border-gray-200 object-cover dark:border-gray-800"
                                />

                                <div class="grid place-content-start gap-1.5">
                                    <p class="text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.settings.themes.edit.image-title'): 

                                        <span class="text-gray-600 transition-all dark:text-gray-300">
                                            @{{ image.title }}
                                        </span>
                                    </p>

                                    <p class="text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.settings.themes.edit.link'): 

                                        <span class="text-gray-600 transition-all dark:text-gray-300">
                                            @{{ image.link }}
                                        </span>
                                    </p>

                                    <p class="text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.settings.themes.edit.image'): 

                                        <span class="text-gray-600 transition-all dark:text-gray-300">
                                            <a
                                                :href="image._previewUrl || ('{{ rtrim(asset('/'), '/') }}/' + image.image)"
                                                :ref="'image_' + image._uid"
                                                target="_blank"
                                                class="text-blue-600 transition-all hover:underline ltr:ml-2 rtl:mr-2"
                                            >
                                                <span :ref="'imageName_' + image._uid">
                                                    @{{ image._previewName || image.image }}
                                                </span>
                                            </a>
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="grid place-content-start gap-1 text-right">
                                <div class="flex items-center gap-x-5">
                                    <p
                                        class="cursor-pointer text-blue-600 transition-all hover:underline"
                                        @click="edit(image, index)"
                                    >
                                        @lang('admin::app.settings.themes.edit.edit')
                                    </p>

                                    <p
                                        class="cursor-pointer text-red-600 transition-all hover:underline"
                                        @click="remove(index)"
                                    >
                                        @lang('admin::app.settings.themes.edit.delete')
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty Page -->
                <div
                    class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                    v-else
                >
                    <img
                        class="h-[120px] w-[120px] p-2 dark:mix-blend-exclusion dark:invert"
                        src="{{ bagisto_asset('images/empty-placeholders/default.svg') }}"
                        alt="@lang('admin::app.settings.themes.edit.slider')"
                    >

                    <div class="flex flex-col items-center gap-1.5">
                        <p class="text-base font-semibold text-gray-400">
                            @lang('admin::app.settings.themes.edit.slider-add-btn')
                        </p>
                        
                        <p class="text-gray-400">
                            @lang('admin::app.settings.themes.edit.slider-description')
                        </p>
                    </div>
                </div>
            </div>

            <x-admin::form v-slot="{ errors, handleSubmit }" as="div">
                <form
                    @submit.prevent="handleSubmit($event, saveSliderImage)"
                    enctype="multipart/form-data"
                    ref="createSliderForm"
                >
                    <x-admin::modal ref="addSliderModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                <template v-if="! isUpdating">
                                    @lang('admin::app.settings.themes.edit.slider-add-btn')
                                </template>

                                <template v-else>
                                    @lang('admin::app.settings.themes.edit.update-slider')
                                </template>
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.themes.edit.image-title')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="{{ $currentLocale->code }}[title]"
                                    rules="required"
                                    v-model="selectedSlider.title"
                                    :placeholder="trans('admin::app.settings.themes.edit.image-title')"
                                    :label="trans('admin::app.settings.themes.edit.image-title')"
                                />

                                <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[title]" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.themes.edit.link')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="{{ $currentLocale->code }}[link]"
                                    v-model="selectedSlider.link"
                                    :placeholder="trans('admin::app.settings.themes.edit.link')"
                                />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.themes.edit.slider-image')
                                </x-admin::form.control-group.label>

                                <div class="hidden">
                                    <x-admin::media.images
                                        ::key="'slider_image_hidden_' + mediaComponentKey"
                                        name="slider_image"
                                        ::uploaded-images='selectedSliderMediaImages'
                                    />
                                </div>

                                <v-media-images
                                    :key="'slider_image_' + mediaComponentKey"
                                    name="slider_image"
                                    :uploaded-images='selectedSliderMediaImages'
                                >
                                </v-media-images>

                                <x-admin::form.control-group.error control-name="slider_image" />
                            </x-admin::form.control-group>

                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.themes.edit.image-size')
                            </p>
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <!-- Save Button -->
                            <button
                                type="button"
                                class="primary-button justify-center"
                                @click="handleSubmit($event, saveSliderImage)"
                            >
                                @lang('admin::app.settings.themes.edit.save-btn')
                            </button>
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </div>
    </script>

    <script type="module">
        app.component('v-image-carousel', {
            template: '#v-image-carousel-template',

            props: ['errors'],

            data() {
                return {
                    sliders: @json($theme->translate($currentLocale->code)['options'] ?? null),

                    deletedSliders: [],

                    selectedSlider: {},

                    selectedSliderMediaImages: [],

                    selectedSliderOriginalImage: null,

                    mediaComponentKey: 0,

                    selectedSliderIndex: null,

                    isUpdating: false,

                    nextUid: 1,
                };
            },
            
            created() {
                if (! this.sliders || ! this.sliders.images) {
                    this.sliders = { images: [] };
                }

                this.sliders.images = this.sliders.images.map((image) => ({
                    ...image,
                    _uid: image._uid ?? this.nextUid++,
                }));
            },

            methods: {
                saveSliderImage(_, { resetForm, setErrors }) {
                    const formData = new FormData(this.$refs.createSliderForm);
                    const sliderImage = formData.get("slider_image[]");
                    const hasUploadedImage = sliderImage instanceof File && sliderImage.name !== '';

                    try {
                        const sliderData = {
                            title: formData.get("{{ $currentLocale->code }}[title]"),
                            link: formData.get("{{ $currentLocale->code }}[link]"),
                        };

                        if (! this.hasSliderImage(formData, hasUploadedImage)) {
                            throw new Error("{{ trans('admin::app.settings.themes.edit.slider-required') }}");
                        }

                        const sliderIndex = this.upsertSlider(sliderData);
                        const slider = this.sliders.images[sliderIndex];

                        if (hasUploadedImage) {
                            this.setFile(sliderImage, slider);
                            this.markSliderImageForDeletion();
                        }

                        resetForm();
                        this.resetSelectedSlider();
                        this.$refs.addSliderModal.toggle();

                    } catch (error) {
                        setErrors({
                            slider_image: [error.message],
                        });
                    }
                },

                upsertSlider(sliderData) {
                    if (this.isUpdating) {
                        this.sliders.images[this.selectedSliderIndex] = {
                            ...this.sliders.images[this.selectedSliderIndex],
                            ...sliderData,
                        };

                        return this.selectedSliderIndex;
                    }

                    this.sliders.images.push({
                        ...sliderData,
                        _uid: this.nextUid++,
                    });

                    return this.sliders.images.length - 1;
                },

                markSliderImageForDeletion() {
                    if (! this.isUpdating || ! this.selectedSliderOriginalImage) {
                        return;
                    }

                    if (this.deletedSliders.some((item) => item.image === this.selectedSliderOriginalImage)) {
                        return;
                    }

                    this.deletedSliders.push({
                        image: this.selectedSliderOriginalImage,
                    });
                },

                hasSliderImage(formData, hasUploadedImage) {
                    if (hasUploadedImage) {
                        return true;
                    }

                    return Array.from(formData.keys()).some((key) => {
                        return key === 'slider_image[]' || key.startsWith('slider_image[');
                    });
                },

                getSliderRef(name) {
                    const ref = this.$refs[name];

                    return Array.isArray(ref) ? ref[0] : ref;
                },

                setFile(file, slider) {
                    const dataTransfer = new DataTransfer();

                    dataTransfer.items.add(file);

                    const previewUrl = URL.createObjectURL(file);
                    slider._previewUrl = previewUrl;
                    slider._previewName = file.name;

                    this.$nextTick(() => {
                        const uid = slider._uid;
                        const linkRef = this.getSliderRef('image_' + uid);
                        const inputRef = this.getSliderRef('imageInput_' + uid);

                        if (linkRef) {
                            linkRef.href = previewUrl;
                        }

                        if (inputRef) {
                            inputRef.files = dataTransfer.files;
                        }
                    });
                },

                remove(index) {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            const slider = this.sliders.images[index];

                            if (! slider) {
                                return;
                            }

                            if (slider.image) {
                                this.deletedSliders.push(slider);
                            }

                            this.sliders.images.splice(index, 1);
                        },
                    });
                },

                create() {
                    this.openSliderModal();
                },

                edit(slider, index) {
                    this.openSliderModal(slider, index);
                },

                openSliderModal(slider = null, index = null) {
                    this.resetSelectedSlider();

                    if (slider) {
                        this.isUpdating = true;
                        this.selectedSliderIndex = index;
                        this.selectedSlider = { ...slider };
                        this.selectedSliderOriginalImage = slider.image;

                        const previewSrc = slider._previewUrl
                            || (slider.image ? '{{ rtrim(asset('/'), '/') }}/' + slider.image : null);

                        this.selectedSliderMediaImages = previewSrc
                            ? [{ id: `slider_image_${index}`, url: previewSrc }]
                            : [];
                    }

                    this.mediaComponentKey++;

                    this.$refs.addSliderModal.toggle();
                },

                resetSelectedSlider() {
                    this.selectedSlider = {};
                    this.selectedSliderMediaImages = [];
                    this.selectedSliderOriginalImage = null;
                    this.selectedSliderIndex = null;
                    this.isUpdating = false;
                },
            },
        });
    </script>
@endPushOnce    
