<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
                'ar_title' => 'معلومات عنا',
                'ar_content' => 'مرحباً بكم في بون بون، وجهتكم الأولى لأفضل المنتجات. نحن نسعى دائماً لتقديم جودة عالية وتجربة تسوق مميزة لعملائنا.',
                'en_title' => 'About Us',
                'en_content' => 'Welcome to Bon Bon, your ultimate destination for the best products. We always strive to provide high quality and a unique shopping experience to our customers.',
            ],
            [
                'url_key' => 'contact-us',
                'ar_title' => 'اتصل بنا',
                'ar_content' => 'يسعدنا تواصلكم معنا في أي وقت. يمكنكم الاتصال بنا عبر البريد الإلكتروني أو الهاتف الموضح في أسفل الموقع لأي استفسارات أو طلبات.',
                'en_title' => 'Contact Us',
                'en_content' => 'We are happy to hear from you at any time. You can contact us via email or phone at the bottom of the site for any inquiries or requests.',
            ],
            [
                'url_key' => 'customer-service',
                'ar_title' => 'خدمة العملاء',
                'ar_content' => 'فريق خدمة العملاء لدينا جاهز للرد على استفساراتكم ومساعدتكم في كل خطوة من خطوات التسوق. رضاكم هو هدفنا الأول.',
                'en_title' => 'Customer Service',
                'en_content' => 'Our customer service team is ready to answer your inquiries and assist you in every step of your shopping journey. Your satisfaction is our primary goal.',
            ],
            [
                'url_key' => 'whats-new',
                'ar_title' => 'ما الجديد',
                'ar_content' => 'تابعوا دائماً أحدث المنتجات والعروض المميزة التي نضيفها باستمرار لتلبية كافة احتياجاتكم وأذواقكم.',
                'en_title' => 'What\'s New',
                'en_content' => 'Always stay tuned for the latest products and special offers that we constantly add to meet all your needs and tastes.',
            ],
            [
                'url_key' => 'terms-of-use',
                'ar_title' => 'شروط الاستخدام',
                'ar_content' => 'استخدامكم لموقع بون بون يعني موافقتكم على الالتزام بشروط الاستخدام الخاصة بنا. يرجى قراءة هذه الشروط بعناية قبل استخدام الموقع.',
                'en_title' => 'Terms of Use',
                'en_content' => 'Your use of the Bon Bon website means you agree to abide by our terms of use. Please read these terms carefully before using the site.',
            ],
            [
                'url_key' => 'terms-conditions',
                'ar_title' => 'الشروط والأحكام',
                'ar_content' => 'تحدد هذه الشروط والأحكام القواعد واللوائح الخاصة باستخدام متجر بون بون. بالوصول إلى هذا الموقع، نفترض أنك تقبل هذه الشروط والأحكام بالكامل.',
                'en_title' => 'Terms & Conditions',
                'en_content' => 'These terms and conditions outline the rules and regulations for the use of the Bon Bon store. By accessing this website we assume you accept these terms and conditions in full.',
            ],
            [
                'url_key' => 'privacy-policy',
                'ar_title' => 'سياسة الخصوصية',
                'ar_content' => 'نحن في بون بون نحترم خصوصيتكم ونلتزم بحماية بياناتكم الشخصية. سياسة الخصوصية توضح كيف نقوم بجمع واستخدام معلوماتكم بطريقة آمنة.',
                'en_title' => 'Privacy Policy',
                'en_content' => 'At Bon Bon, we respect your privacy and are committed to protecting your personal data. The privacy policy explains how we collect and use your information securely.',
            ],
            [
                'url_key' => 'payment-policy',
                'ar_title' => 'سياسة الدفع',
                'ar_content' => 'نوفر لكم طرق دفع متعددة وآمنة لتسهيل عملية الشراء. تشمل طرق الدفع البطاقات الائتمانية والدفع عند الاستلام حسب توفرها.',
                'en_title' => 'Payment Policy',
                'en_content' => 'We provide multiple secure payment methods to facilitate your purchase process. Payment methods include credit cards and cash on delivery where available.',
            ],
            [
                'url_key' => 'shipping-policy',
                'ar_title' => 'سياسة الشحن',
                'ar_content' => 'نحرص على توصيل طلباتكم في أسرع وقت ممكن. تعتمد تكلفة ومدة الشحن على موقعكم وحجم الطلب، وسيتم توضيحها أثناء إتمام الطلب.',
                'en_title' => 'Shipping Policy',
                'en_content' => 'We are keen to deliver your orders as soon as possible. Shipping cost and duration depend on your location and order size, and will be clarified during checkout.',
            ],
            [
                'url_key' => 'refund-policy',
                'ar_title' => 'سياسة الاسترداد',
                'ar_content' => 'في حال لم تكونوا راضين عن مشترياتكم، يمكنكم طلب استرداد المبلغ ضمن الشروط المحددة وخلال الفترة الزمنية المسموح بها حسب سياسة المتجر.',
                'en_title' => 'Refund Policy',
                'en_content' => 'If you are not satisfied with your purchase, you can request a refund under the specified conditions and within the permitted time period according to the store\'s policy.',
            ],
            [
                'url_key' => 'return-policy',
                'ar_title' => 'سياسة الإرجاع',
                'ar_content' => 'يمكنكم إرجاع المنتجات التي لم تُستخدم والموجودة في حالتها الأصلية خلال المدة المحددة للإرجاع. يرجى التواصل مع خدمة العملاء لبدء عملية الإرجاع.',
                'en_title' => 'Return Policy',
                'en_content' => 'You can return unused products in their original condition within the specified return period. Please contact customer service to initiate the return process.',
            ]
        ];

        foreach ($pages as $pageData) {
            $arTranslation = CmsPageTranslation::where('url_key', $pageData['url_key'])->where('locale', 'ar')->first();
            $enTranslation = CmsPageTranslation::where('url_key', $pageData['url_key'])->where('locale', 'en')->first();
            
            $cmsPageId = null;

            if ($arTranslation) {
                $cmsPageId = $arTranslation->cms_page_id;
                $arTranslation->update([
                    'page_title' => $pageData['ar_title'],
                    'html_content' => '<div class="cms-page-content"><p>' . $pageData['ar_content'] . '</p></div>'
                ]);
            }

            if ($enTranslation) {
                $cmsPageId = $cmsPageId ?? $enTranslation->cms_page_id;
                $enTranslation->update([
                    'page_title' => $pageData['en_title'],
                    'html_content' => '<div class="cms-page-content"><p>' . $pageData['en_content'] . '</p></div>'
                ]);
            }
            
            // If the page doesn't exist at all, we create a core CMS page.
            if (!$cmsPageId) {
                // Determine if there is any CMS page that already has this url_key to link to
                $existingTranslation = CmsPageTranslation::where('url_key', $pageData['url_key'])->first();
                if ($existingTranslation) {
                    $cmsPageId = $existingTranslation->cms_page_id;
                } else {
                    $cmsPageId = DB::table('cms_pages')->insertGetId([
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    DB::table('cms_page_channels')->insert([
                        'cms_page_id' => $cmsPageId,
                        'channel_id' => 1
                    ]);
                }
            }

            if (!$arTranslation) {
                DB::table('cms_page_translations')->insert([
                    'cms_page_id' => $cmsPageId,
                    'locale' => 'ar',
                    'url_key' => $pageData['url_key'],
                    'page_title' => $pageData['ar_title'],
                    'html_content' => '<div class="cms-page-content"><p>' . $pageData['ar_content'] . '</p></div>',
                    'meta_title' => $pageData['ar_title'],
                    'meta_description' => $pageData['ar_title'],
                    'meta_keywords' => str_replace(' ', ',', $pageData['ar_title'])
                ]);
            }

            if (!$enTranslation) {
                DB::table('cms_page_translations')->insert([
                    'cms_page_id' => $cmsPageId,
                    'locale' => 'en',
                    'url_key' => $pageData['url_key'],
                    'page_title' => $pageData['en_title'],
                    'html_content' => '<div class="cms-page-content"><p>' . $pageData['en_content'] . '</p></div>',
                    'meta_title' => $pageData['en_title'],
                    'meta_description' => $pageData['en_title'],
                    'meta_keywords' => str_replace(' ', ',', $pageData['en_title'])
                ]);
            }
        }
    }
}
