<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Webkul\CMS\Models\CmsPage;
use Webkul\CMS\Models\CmsPageTranslation;
use Carbon\Carbon;

class BonBonCmsPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pages = [
            [
                'url_key' => 'about-us',
                'title' => 'معلومات عنا',
                'content' => 'مرحباً بكم في بون بون، وجهتكم الأولى لأفضل المنتجات. نحن نسعى دائماً لتقديم جودة عالية وتجربة تسوق مميزة لعملائنا.',
            ],
            [
                'url_key' => 'contact-us',
                'title' => 'اتصل بنا',
                'content' => 'يسعدنا تواصلكم معنا في أي وقت. يمكنكم الاتصال بنا عبر البريد الإلكتروني أو الهاتف الموضح في أسفل الموقع لأي استفسارات أو طلبات.',
            ],
            [
                'url_key' => 'customer-service',
                'title' => 'خدمة العملاء',
                'content' => 'فريق خدمة العملاء لدينا جاهز للرد على استفساراتكم ومساعدتكم في كل خطوة من خطوات التسوق. رضاكم هو هدفنا الأول.',
            ],
            [
                'url_key' => 'whats-new',
                'title' => 'ما الجديد',
                'content' => 'تابعوا دائماً أحدث المنتجات والعروض المميزة التي نضيفها باستمرار لتلبية كافة احتياجاتكم وأذواقكم.',
            ],
            [
                'url_key' => 'terms-of-use',
                'title' => 'شروط الاستخدام',
                'content' => 'استخدامكم لموقع بون بون يعني موافقتكم على الالتزام بشروط الاستخدام الخاصة بنا. يرجى قراءة هذه الشروط بعناية قبل استخدام الموقع.',
            ],
            [
                'url_key' => 'terms-conditions',
                'title' => 'الشروط والأحكام',
                'content' => 'تحدد هذه الشروط والأحكام القواعد واللوائح الخاصة باستخدام متجر بون بون. بالوصول إلى هذا الموقع، نفترض أنك تقبل هذه الشروط والأحكام بالكامل.',
            ],
            [
                'url_key' => 'privacy-policy',
                'title' => 'سياسة الخصوصية',
                'content' => 'نحن في بون بون نحترم خصوصيتكم ونلتزم بحماية بياناتكم الشخصية. سياسة الخصوصية توضح كيف نقوم بجمع واستخدام معلوماتكم بطريقة آمنة.',
            ],
            [
                'url_key' => 'payment-policy',
                'title' => 'سياسة الدفع',
                'content' => 'نوفر لكم طرق دفع متعددة وآمنة لتسهيل عملية الشراء. تشمل طرق الدفع البطاقات الائتمانية والدفع عند الاستلام حسب توفرها.',
            ],
            [
                'url_key' => 'shipping-policy',
                'title' => 'سياسة الشحن',
                'content' => 'نحرص على توصيل طلباتكم في أسرع وقت ممكن. تعتمد تكلفة ومدة الشحن على موقعكم وحجم الطلب، وسيتم توضيحها أثناء إتمام الطلب.',
            ],
            [
                'url_key' => 'refund-policy',
                'title' => 'سياسة الاسترداد',
                'content' => 'في حال لم تكونوا راضين عن مشترياتكم، يمكنكم طلب استرداد المبلغ ضمن الشروط المحددة وخلال الفترة الزمنية المسموح بها حسب سياسة المتجر.',
            ],
            [
                'url_key' => 'return-policy',
                'title' => 'سياسة الإرجاع',
                'content' => 'يمكنكم إرجاع المنتجات التي لم تُستخدم والموجودة في حالتها الأصلية خلال المدة المحددة للإرجاع. يرجى التواصل مع خدمة العملاء لبدء عملية الإرجاع.',
            ]
        ];

        foreach ($pages as $pageData) {
            $pageTranslation = CmsPageTranslation::where('url_key', $pageData['url_key'])->first();
            
            if ($pageTranslation) {
                // Update existing page translation
                $pageTranslation->update([
                    'page_title' => $pageData['title'],
                    'html_content' => '<div class="cms-page-content"><p>' . $pageData['content'] . '</p></div>'
                ]);
            } else {
                // Create new CMS Page
                $cmsPageId = DB::table('cms_pages')->insertGetId([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                // Link to default channel (channel_id = 1)
                DB::table('cms_page_channels')->insert([
                    'cms_page_id' => $cmsPageId,
                    'channel_id' => 1
                ]);
                
                // Arabic Translation
                DB::table('cms_page_translations')->insert([
                    'cms_page_id' => $cmsPageId,
                    'locale' => 'ar',
                    'url_key' => $pageData['url_key'],
                    'page_title' => $pageData['title'],
                    'html_content' => '<div class="cms-page-content"><p>' . $pageData['content'] . '</p></div>',
                    'meta_title' => $pageData['title'],
                    'meta_description' => $pageData['title'],
                    'meta_keywords' => str_replace(' ', ',', $pageData['title'])
                ]);
                
                // English Translation (to ensure it works in both locales)
                DB::table('cms_page_translations')->insert([
                    'cms_page_id' => $cmsPageId,
                    'locale' => 'en',
                    'url_key' => $pageData['url_key'],
                    'page_title' => $pageData['title'],
                    'html_content' => '<div class="cms-page-content"><p>' . $pageData['content'] . '</p></div>',
                    'meta_title' => $pageData['title'],
                    'meta_description' => $pageData['title'],
                    'meta_keywords' => str_replace(' ', ',', $pageData['title'])
                ]);
            }
        }
    }
}
