<?php

namespace Webkul\Theme\Repositories;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;
use Webkul\Core\Eloquent\Repository;
use Webkul\Theme\Contracts\ThemeCustomization;

class ThemeCustomizationRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return ThemeCustomization::class;
    }

    /**
     * Update the specified theme
     *
     * @param  array  $data
     * @param  int  $id
     */
    public function update($data, $id): ThemeCustomization
    {
        $locale = core()->getRequestedLocaleCode();

        if ($data['type'] == 'static_content') {
            $config = [
                'HTML.Allowed' => null,
                'HTML.ForbiddenElements' => 'script,iframe,form',
                'CSS.AllowedProperties' => null,
            ];

            $data[$locale]['options']['html'] = Purify::config($config)->clean($data[$locale]['options']['html']);

            $data[$locale]['options']['css'] = Purify::config($config)->clean($data[$locale]['options']['css']);
        }

        if (in_array($data['type'], ['image_carousel', 'services_content'])) {
            unset($data[$locale]['options']);
        }

        $theme = parent::update($data, $id);

        if (in_array($data['type'], ['image_carousel', 'services_content'])) {
            $this->uploadImage(request()->all(), $theme);
        }

        return $theme;
    }

    /**
     * Mass update the status of themes in the repository.
     *
     * This method updates multiple records in the database based on the provided
     * theme IDs.
     *
     * @param  int  $themeIds
     * @return int The number of records updated.
     */
    public function massUpdateStatus(array $data, array $themeIds)
    {
        return $this->model->whereIn('id', $themeIds)->update($data);
    }

    /**
     * Upload images
     *
     * @return void|string|RedirectResponse
     */
    public function uploadImage(array $data, ThemeCustomization $theme)
    {
        $locale = core()->getRequestedLocaleCode();
        $isStaticContent = ($data['type'] ?? $theme->type) === 'static_content';
        $deletedSliders = $data[$locale]['deleted_sliders'] ?? [];

        if (! isset($data[$locale]['options'])) {
            if (! $isStaticContent) {
                $translatedModel = $theme->translateOrNew($locale);
                $translatedModel->options = [];
                $translatedModel->theme_customization_id = $theme->id;
                $translatedModel->save();

                $this->deleteUnusedSliderImages($deletedSliders, $theme);
            }

            return;
        }

        $options = [];

        foreach ($data[$locale]['options'] as $image) {
            if (isset($image['service_icon'])) {
                $options['services'][] = [
                    'service_icon' => $image['service_icon'],
                    'description' => $image['description'],
                    'title' => $image['title'],
                ];
            } elseif (($image['image'] ?? null) instanceof UploadedFile) {
                try {
                    $path = 'theme/'.$theme->id.'/'.Str::random(40).'.webp';

                    $encoded = image_manager()->read($image['image'])->encodeByExtension('webp');

                    Storage::put($path, (string) $encoded);
                } catch (\Exception $e) {
                    report($e);

                    session()->flash('error', $e->getMessage());

                    return redirect()->back();
                }

                if ($isStaticContent) {
                    return Storage::url($path);
                }

                $options['images'][] = [
                    'image' => 'storage/'.$path,
                    'link' => $image['link'] ?? '',
                    'title' => $image['title'] ?? '',
                ];
            } else {
                $options['images'][] = $image;
            }
        }

        $translatedModel = $theme->translateOrNew($locale);
        $translatedModel->options = $options ?? [];
        $translatedModel->theme_customization_id = $theme->id;
        $translatedModel->save();

        $this->deleteUnusedSliderImages($deletedSliders, $theme);
    }

    /**
     * Delete removed slider images only after save and only if no other locale still references them.
     */
    protected function deleteUnusedSliderImages(array $deletedSliders, ThemeCustomization $theme): void
    {
        if (empty($deletedSliders)) {
            return;
        }

        $referencedPaths = [];

        foreach ($theme->translations()->get() as $translation) {
            foreach ($translation->options['images'] ?? [] as $slider) {
                if (! empty($slider['image']) && is_string($slider['image'])) {
                    $referencedPaths[] = $this->normalizeStoragePath($slider['image']);
                }
            }
        }

        foreach ($deletedSliders as $slider) {
            if (empty($slider['image']) || ! is_string($slider['image'])) {
                continue;
            }

            $storagePath = $this->normalizeStoragePath($slider['image']);

            if ($storagePath === '' || in_array($storagePath, $referencedPaths, true)) {
                continue;
            }

            Storage::delete($storagePath);
        }
    }

    /**
     * Strip a leading storage/ prefix from a stored image path.
     */
    protected function normalizeStoragePath(string $path): string
    {
        return (string) preg_replace('#^storage/#', '', ltrim($path, '/'));
    }
}
