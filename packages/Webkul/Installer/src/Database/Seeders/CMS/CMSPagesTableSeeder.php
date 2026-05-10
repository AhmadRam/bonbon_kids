<?php

namespace Webkul\Installer\Database\Seeders\CMS;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CMSPagesTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        DB::table('cms_pages')->delete();
        DB::table('cms_page_translations')->delete();
        DB::table('cms_page_channels')->delete();

        // We insert 11 pages
        $pageIds = range(1, 11);
        $cmsPagesData = array_map(function ($id) {
            return [
                'id' => $id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }, $pageIds);
        DB::table('cms_pages')->insert($cmsPagesData);

        $pagesData = [
            1 => [
                'url_key' => 'about-us',
                'ar_title' => 'معلومات عنا',
                'ar_content' => 'مرحباً بكم في بون بون، وجهتكم الأولى لأفضل المنتجات. نحن نسعى دائماً لتقديم جودة عالية وتجربة تسوق مميزة لعملائنا.',
                'en_title' => 'About Us',
                'en_content' => 'Welcome to Bon Bon, your ultimate destination for the best products. We always strive to provide high quality and a unique shopping experience to our customers.',
            ],
            2 => [
                'url_key' => 'return-policy',
                'ar_title' => 'سياسة الإرجاع',
                'ar_content' => 'يمكنكم إرجاع المنتجات التي لم تُستخدم والموجودة في حالتها الأصلية خلال المدة المحددة للإرجاع. يرجى التواصل مع خدمة العملاء لبدء عملية الإرجاع.',
                'en_title' => 'Return Policy',
                'en_content' => 'You can return unused products in their original condition within the specified return period. Please contact customer service to initiate the return process.',
            ],
            3 => [
                'url_key' => 'refund-policy',
                'ar_title' => 'سياسة الاسترداد',
                'ar_content' => 'في حال لم تكونوا راضين عن مشترياتكم، يمكنكم طلب استرداد المبلغ ضمن الشروط المحددة وخلال الفترة الزمنية المسموح بها حسب سياسة المتجر.',
                'en_title' => 'Refund Policy',
                'en_content' => 'If you are not satisfied with your purchase, you can request a refund under the specified conditions and within the permitted time period according to the store\'s policy.',
            ],
            4 => [
                'url_key' => 'terms-conditions',
                'ar_title' => 'الشروط والأحكام',
                'ar_content' => 'تحدد هذه الشروط والأحكام القواعد واللوائح الخاصة باستخدام متجر بون بون. بالوصول إلى هذا الموقع، نفترض أنك تقبل هذه الشروط والأحكام بالكامل.',
                'en_title' => 'Terms & Conditions',
                'en_content' => 'These terms and conditions outline the rules and regulations for the use of the Bon Bon store. By accessing this website we assume you accept these terms and conditions in full.',
            ],
            5 => [
                'url_key' => 'terms-of-use',
                'ar_title' => 'شروط الاستخدام',
                'ar_content' => 'استخدامكم لموقع بون بون يعني موافقتكم على الالتزام بشروط الاستخدام الخاصة بنا. يرجى قراءة هذه الشروط بعناية قبل استخدام الموقع.',
                'en_title' => 'Terms of Use',
                'en_content' => 'Your use of the Bon Bon website means you agree to abide by our terms of use. Please read these terms carefully before using the site.',
            ],
            6 => [
                'url_key' => 'customer-service',
                'ar_title' => 'خدمة العملاء',
                'ar_content' => 'فريق خدمة العملاء لدينا جاهز للرد على استفساراتكم ومساعدتكم في كل خطوة من خطوات التسوق. رضاكم هو هدفنا الأول.',
                'en_title' => 'Customer Service',
                'en_content' => 'Our customer service team is ready to answer your inquiries and assist you in every step of your shopping journey. Your satisfaction is our primary goal.',
            ],
            7 => [
                'url_key' => 'whats-new',
                'ar_title' => 'ما الجديد',
                'ar_content' => 'تابعوا دائماً أحدث المنتجات والعروض المميزة التي نضيفها باستمرار لتلبية كافة احتياجاتكم وأذواقكم.',
                'en_title' => 'What\'s New',
                'en_content' => 'Always stay tuned for the latest products and special offers that we constantly add to meet all your needs and tastes.',
            ],
            8 => [
                'url_key' => 'payment-policy',
                'ar_title' => 'سياسة الدفع',
                'ar_content' => 'نوفر لكم طرق دفع متعددة وآمنة لتسهيل عملية الشراء. تشمل طرق الدفع البطاقات الائتمانية والدفع عند الاستلام حسب توفرها.',
                'en_title' => 'Payment Policy',
                'en_content' => 'We provide multiple secure payment methods to facilitate your purchase process. Payment methods include credit cards and cash on delivery where available.',
            ],
            9 => [
                'url_key' => 'shipping-policy',
                'ar_title' => 'سياسة الشحن',
                'ar_content' => 'نحرص على توصيل طلباتكم في أسرع وقت ممكن. تعتمد تكلفة ومدة الشحن على موقعكم وحجم الطلب، وسيتم توضيحها أثناء إتمام الطلب.',
                'en_title' => 'Shipping Policy',
                'en_content' => 'We are keen to deliver your orders as soon as possible. Shipping cost and duration depend on your location and order size, and will be clarified during checkout.',
            ],
            10 => [
                'url_key' => 'privacy-policy',
                'ar_title' => 'سياسة الخصوصية',
                'ar_content' => 'نحن في بون بون نحترم خصوصيتكم ونلتزم بحماية بياناتكم الشخصية. سياسة الخصوصية توضح كيف نقوم بجمع واستخدام معلوماتكم بطريقة آمنة.',
                'en_title' => 'Privacy Policy',
                'en_content' => 'At Bon Bon, we respect your privacy and are committed to protecting your personal data. The privacy policy explains how we collect and use your information securely.',
            ],
            11 => [
                'url_key' => 'contact-us',
                'ar_title' => 'اتصل بنا',
                'ar_content' => 'يسعدنا تواصلكم معنا في أي وقت. يمكنكم الاتصال بنا عبر البريد الإلكتروني أو الهاتف الموضح في أسفل الموقع لأي استفسارات أو طلبات.',
                'en_title' => 'Contact Us',
                'en_content' => 'We are happy to hear from you at any time. You can contact us via email or phone at the bottom of the site for any inquiries or requests.',
            ],
        ];

        $translations = [];
        foreach ($pagesData as $id => $data) {
            // Arabic Translation
            $translations[] = [
                'locale' => 'ar',
                'cms_page_id' => $id,
                'url_key' => $data['url_key'],
                'html_content' => '<div class="static-container"><div class="mb-5">' . $data['ar_content'] . '</div></div>',
                'page_title' => $data['ar_title'],
                'meta_title' => $data['ar_title'],
                'meta_description' => $data['ar_title'],
                'meta_keywords' => str_replace(' ', ',', $data['ar_title']),
            ];

            // English Translation
            $translations[] = [
                'locale' => 'en',
                'cms_page_id' => $id,
                'url_key' => $data['url_key'],
                'html_content' => '<div class="static-container"><div class="mb-5">' . $data['en_content'] . '</div></div>',
                'page_title' => $data['en_title'],
                'meta_title' => $data['en_title'],
                'meta_description' => $data['en_title'],
                'meta_keywords' => str_replace(' ', ',', $data['en_title']),
            ];
        }

        DB::table('cms_page_translations')->insert($translations);

        $channelsData = array_map(function ($id) {
            return [
                'cms_page_id' => $id,
                'channel_id' => 1,
            ];
        }, $pageIds);
        DB::table('cms_page_channels')->insert($channelsData);
    }
}
