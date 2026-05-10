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
                'ar_content' => '
                    <h3 class="mb-3 text-xl font-bold">مرحباً بكم في بون بون</h3>
                    <p class="mb-4 text-gray-600">بون بون هو وجهتكم الأولى والمفضلة لكل ما يخص عالم الأطفال المليء بالمرح والخيال. نحن لسنا مجرد متجر ألعاب تقليدي، بل نحن شركاء في رسم الابتسامة على وجوه أطفالكم وتنمية مهاراتهم الإبداعية والحركية من خلال تشكيلة واسعة من الألعاب التعليمية والترفيهية المبتكرة.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">رؤيتنا ومهمتنا</h3>
                    <p class="mb-4 text-gray-600">تتمثل رؤيتنا في تقديم بيئة تسوق آمنة وموثوقة توفر أفضل المنتجات العالمية والمحلية التي تلبي احتياجات الأطفال في مختلف الفئات العمرية. نحن نؤمن بأن اللعب هو الوسيلة الأفضل للتعلم، ولذلك نحرص على اختيار ألعاب تحفز الذكاء والإبداع.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">لماذا تختار بون بون؟</h3>
                    <ul class="list-disc pl-5 mb-4 text-gray-600 space-y-2">
                        <li><strong>جودة لا تضاهى:</strong> جميع منتجاتنا مطابقة لمواصفات الجودة والسلامة العالمية.</li>
                        <li><strong>تنوع واسع:</strong> من الألعاب التعليمية إلى ألعاب الحركة، لدينا كل ما يحتاجه طفلك.</li>
                        <li><strong>خدمة عملاء استثنائية:</strong> فريقنا متواجد دائماً لمساعدتكم في اختيار الأنسب.</li>
                    </ul>',
                'en_title' => 'About Us',
                'en_content' => '
                    <h3 class="mb-3 text-xl font-bold">Welcome to Bon Bon</h3>
                    <p class="mb-4 text-gray-600">Bon Bon is your premier and favorite destination for everything related to the children\'s world, full of fun and imagination. We are not just a traditional toy store; we are partners in bringing smiles to your children\'s faces and developing their creative and motor skills through a wide range of innovative educational and entertaining toys.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">Our Vision & Mission</h3>
                    <p class="mb-4 text-gray-600">Our vision is to provide a safe and reliable shopping environment that offers the best international and local products to meet the needs of children of all age groups. We believe that playing is the best way to learn, which is why we carefully select toys that stimulate intelligence and creativity.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">Why Choose Bon Bon?</h3>
                    <ul class="list-disc pl-5 mb-4 text-gray-600 space-y-2">
                        <li><strong>Unmatched Quality:</strong> All our products meet international quality and safety standards.</li>
                        <li><strong>Wide Variety:</strong> From educational toys to action figures, we have everything your child needs.</li>
                        <li><strong>Exceptional Customer Service:</strong> Our team is always here to help you make the best choice.</li>
                    </ul>',
            ],
            2 => [
                'url_key' => 'return-policy',
                'ar_title' => 'سياسة الإرجاع',
                'ar_content' => '
                    <h3 class="mb-3 text-xl font-bold">إرجاع سهل ومريح</h3>
                    <p class="mb-4 text-gray-600">نحن في بون بون نتفهم أنكم قد تغيرون رأيكم بعد الشراء، ولذلك نوفر لكم سياسة إرجاع مرنة تضمن حقوقكم وتسعى لرضاكم التام.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">شروط الإرجاع:</h3>
                    <ul class="list-decimal pl-5 mb-4 text-gray-600 space-y-2">
                        <li>يجب طلب الإرجاع خلال فترة لا تتجاوز 14 يوماً من تاريخ استلام الطلب.</li>
                        <li>يجب أن يكون المنتج في حالته الأصلية، غير مستخدم، وبغلافه الأصلي مع كافة الملحقات والملصقات.</li>
                        <li>المنتجات التي تم تجميعها أو تركيبها لا يمكن إرجاعها إلا في حال وجود عيب مصنعي.</li>
                        <li>يجب إرفاق فاتورة الشراء الأصلية مع الطلب.</li>
                    </ul>
                    <p class="mt-4 text-gray-600">لتقديم طلب إرجاع، يرجى التواصل مع فريق خدمة العملاء وتزويدهم برقم الطلب وتفاصيل المنتج المراد إرجاعه.</p>',
                'en_title' => 'Return Policy',
                'en_content' => '
                    <h3 class="mb-3 text-xl font-bold">Easy and Convenient Returns</h3>
                    <p class="mb-4 text-gray-600">At Bon Bon, we understand that you may change your mind after a purchase. Therefore, we offer a flexible return policy that protects your rights and strives for your complete satisfaction.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">Return Conditions:</h3>
                    <ul class="list-decimal pl-5 mb-4 text-gray-600 space-y-2">
                        <li>Return requests must be made within 14 days of receiving the order.</li>
                        <li>The product must be in its original condition, unused, and in its original packaging with all accessories and tags.</li>
                        <li>Products that have been assembled cannot be returned unless there is a manufacturing defect.</li>
                        <li>The original purchase receipt must be included with the request.</li>
                    </ul>
                    <p class="mt-4 text-gray-600">To submit a return request, please contact our customer service team and provide them with the order number and details of the product to be returned.</p>',
            ],
            3 => [
                'url_key' => 'refund-policy',
                'ar_title' => 'سياسة الاسترداد',
                'ar_content' => '
                    <h3 class="mb-3 text-xl font-bold">استرداد المبالغ</h3>
                    <p class="mb-4 text-gray-600">نلتزم في بون بون بإعادة المبالغ المستحقة لعملائنا في أقرب وقت ممكن عند إرجاع المنتجات وفقاً لسياسة الإرجاع الخاصة بنا.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">كيفية استرداد الأموال:</h3>
                    <ul class="list-disc pl-5 mb-4 text-gray-600 space-y-2">
                        <li><strong>الدفع بالبطاقة الائتمانية:</strong> سيتم إرجاع المبلغ إلى نفس البطاقة المستخدمة في الشراء خلال 7 إلى 14 يوم عمل حسب سياسة البنك الخاص بك.</li>
                        <li><strong>الدفع عند الاستلام:</strong> سيتم تحويل المبلغ إلى حسابكم البنكي أو إضافته كرصيد في محفظتكم داخل المتجر لاستخدامه في مشتريات مستقبلية.</li>
                    </ul>
                    <p class="text-gray-600">ملاحظة: رسوم الشحن الأصلية غير قابلة للاسترداد، وقد يتم خصم رسوم إضافية لعملية الشحن العكسي ما لم يكن سبب الإرجاع هو خطأ من المتجر أو عيب مصنعي.</p>',
                'en_title' => 'Refund Policy',
                'en_content' => '
                    <h3 class="mb-3 text-xl font-bold">Refund Process</h3>
                    <p class="mb-4 text-gray-600">At Bon Bon, we are committed to refunding amounts due to our customers as quickly as possible upon returning products in accordance with our return policy.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">How Refunds are Issued:</h3>
                    <ul class="list-disc pl-5 mb-4 text-gray-600 space-y-2">
                        <li><strong>Credit Card Payments:</strong> The amount will be refunded to the same card used for the purchase within 7 to 14 business days, depending on your bank\'s policy.</li>
                        <li><strong>Cash on Delivery:</strong> The amount will be transferred to your bank account or added as store credit to your account for future purchases.</li>
                    </ul>
                    <p class="text-gray-600">Note: Original shipping fees are non-refundable, and a return shipping fee may be deducted unless the reason for the return is a store error or a manufacturing defect.</p>',
            ],
            4 => [
                'url_key' => 'terms-conditions',
                'ar_title' => 'الشروط والأحكام',
                'ar_content' => '
                    <h3 class="mb-3 text-xl font-bold">مقدمة</h3>
                    <p class="mb-4 text-gray-600">مرحباً بكم في متجر بون بون. تحدد هذه الشروط والأحكام القواعد واللوائح الخاصة باستخدام موقعنا الإلكتروني والخدمات المقدمة من خلاله. باستخدام هذا الموقع، فإنك توافق على الالتزام الكامل بهذه الشروط.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">1. التسجيل والحساب:</h3>
                    <p class="mb-4 text-gray-600">يجب على المستخدمين تقديم معلومات دقيقة ومحدثة عند التسجيل. يتحمل المستخدم مسؤولية الحفاظ على سرية بيانات حسابه وكلمة المرور الخاصة به.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">2. المنتجات والأسعار:</h3>
                    <p class="mb-4 text-gray-600">نحن نحتفظ بالحق في تعديل أسعار المنتجات أو إيقاف توفرها في أي وقت دون إشعار مسبق. جميع الأوصاف والصور هي تقريبية وتهدف لتوضيح شكل المنتج.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">3. الطلبات:</h3>
                    <p class="mb-4 text-gray-600">يحق لإدارة المتجر رفض أو إلغاء أي طلب لعدة أسباب، منها عدم توفر المنتج، أو اكتشاف خطأ في السعر، أو اشتباه في عملية احتيالية.</p>',
                'en_title' => 'Terms & Conditions',
                'en_content' => '
                    <h3 class="mb-3 text-xl font-bold">Introduction</h3>
                    <p class="mb-4 text-gray-600">Welcome to the Bon Bon store. These terms and conditions outline the rules and regulations for the use of our website and services. By using this site, you fully agree to be bound by these terms.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">1. Registration and Account:</h3>
                    <p class="mb-4 text-gray-600">Users must provide accurate and up-to-date information upon registration. The user is responsible for maintaining the confidentiality of their account details and password.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">2. Products and Pricing:</h3>
                    <p class="mb-4 text-gray-600">We reserve the right to modify product prices or discontinue their availability at any time without prior notice. All descriptions and images are approximate and intended to illustrate the product.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">3. Orders:</h3>
                    <p class="mb-4 text-gray-600">Store management has the right to refuse or cancel any order for various reasons, including product unavailability, price errors, or suspected fraudulent activity.</p>',
            ],
            5 => [
                'url_key' => 'terms-of-use',
                'ar_title' => 'شروط الاستخدام',
                'ar_content' => '
                    <h3 class="mb-3 text-xl font-bold">شروط استخدام الموقع</h3>
                    <p class="mb-4 text-gray-600">يهدف متجر بون بون إلى توفير تجربة مستخدم سلسة وآمنة. يُمنع استخدام هذا الموقع لأي أغراض غير قانونية أو غير مصرح بها. يجب أن لا تقوم بنقل أي فيروسات أو شفرات ذات طابع مدمر.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">حقوق الملكية الفكرية</h3>
                    <p class="mb-4 text-gray-600">جميع المحتويات المتوفرة على الموقع، بما في ذلك النصوص، الرسومات، الشعارات، الصور، والبرمجيات هي ملك لمتجر بون بون وهي محمية بموجب قوانين حقوق الطبع والنشر الدولية والمحلية.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">تعليقات المستخدمين ومراجعاتهم</h3>
                    <p class="text-gray-600">نرحب بتعليقاتكم ومراجعاتكم للمنتجات، ولكننا نحتفظ بالحق في إزالة أي محتوى يحتوي على لغة مسيئة، أو سب، أو ترويج لجهات أخرى.</p>',
                'en_title' => 'Terms of Use',
                'en_content' => '
                    <h3 class="mb-3 text-xl font-bold">Website Terms of Use</h3>
                    <p class="mb-4 text-gray-600">The Bon Bon store aims to provide a smooth and safe user experience. You may not use this site for any illegal or unauthorized purposes. You must not transmit any worms, viruses, or any code of a destructive nature.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">Intellectual Property Rights</h3>
                    <p class="mb-4 text-gray-600">All content available on the site, including text, graphics, logos, images, and software, is the property of Bon Bon store and is protected by international and local copyright laws.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">User Comments and Reviews</h3>
                    <p class="text-gray-600">We welcome your comments and product reviews, but we reserve the right to remove any content that contains offensive language, insults, or promotes third parties.</p>',
            ],
            6 => [
                'url_key' => 'customer-service',
                'ar_title' => 'خدمة العملاء',
                'ar_content' => '
                    <h3 class="mb-3 text-xl font-bold">نحن هنا لخدمتكم</h3>
                    <p class="mb-4 text-gray-600">في بون بون، رضاكم هو أولويتنا القصوى. فريق خدمة العملاء لدينا مدرب على أعلى مستوى لضمان حصولكم على تجربة تسوق خالية من المتاعب.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">كيف يمكننا مساعدتكم؟</h3>
                    <ul class="list-disc pl-5 mb-4 text-gray-600 space-y-2">
                        <li><strong>الاستفسار عن المنتجات:</strong> توضيح تفاصيل الألعاب، الفئات العمرية المناسبة، وكيفية التركيب.</li>
                        <li><strong>تتبع الطلبات:</strong> مساعدتكم في معرفة حالة الطلب وموعد التوصيل المتوقع.</li>
                        <li><strong>الدعم الفني:</strong> حل أي مشاكل تقنية تواجهكم أثناء تصفح المتجر أو إتمام عملية الدفع.</li>
                        <li><strong>المطالبات والشكاوى:</strong> التعامل السريع والفعال مع أي مشكلة قد تحدث بعد الشراء.</li>
                    </ul>
                    <p class="text-gray-600">يمكنكم التواصل معنا عبر القنوات المتاحة في صفحة "اتصل بنا" أو من خلال أيقونة الدردشة الحية أسفل الشاشة (إن وجدت).</p>',
                'en_title' => 'Customer Service',
                'en_content' => '
                    <h3 class="mb-3 text-xl font-bold">We Are Here to Serve You</h3>
                    <p class="mb-4 text-gray-600">At Bon Bon, your satisfaction is our top priority. Our customer service team is highly trained to ensure you have a hassle-free shopping experience.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">How Can We Help You?</h3>
                    <ul class="list-disc pl-5 mb-4 text-gray-600 space-y-2">
                        <li><strong>Product Inquiries:</strong> Clarifying toy details, suitable age groups, and assembly instructions.</li>
                        <li><strong>Order Tracking:</strong> Assisting you in checking your order status and expected delivery time.</li>
                        <li><strong>Technical Support:</strong> Resolving any technical issues you encounter while browsing or checking out.</li>
                        <li><strong>Claims & Complaints:</strong> Fast and effective handling of any issues that may arise post-purchase.</li>
                    </ul>
                    <p class="text-gray-600">You can contact us via the channels available on the "Contact Us" page or through the live chat icon at the bottom of the screen (if available).</p>',
            ],
            7 => [
                'url_key' => 'whats-new',
                'ar_title' => 'ما الجديد',
                'ar_content' => '
                    <h3 class="mb-3 text-xl font-bold">أحدث التشكيلات والإضافات</h3>
                    <p class="mb-4 text-gray-600">عالم الألعاب يتطور باستمرار، ونحن في بون بون نحرص على مواكبة كل جديد! اكتشفوا أحدث المنتجات التي قمنا بإضافتها مؤخراً لمتجرنا.</p>
                    <p class="mb-4 text-gray-600">من ألعاب الذكاء الاصطناعي التفاعلية، والألعاب التعليمية المصممة بطرق حديثة، إلى الشخصيات الكرتونية المفضلة لدى الأطفال التي صدرت هذا العام. نحن نجلب لكم الترفيه المبتكر إلى عتبة بابكم.</p>
                    <p class="mb-4 text-gray-600 font-bold">تابعوا هذه الصفحة باستمرار، ولا تنسوا الاشتراك في نشرتنا البريدية لتبقوا على إطلاع دائم بالعروض الحصرية والإطلاقات الجديدة.</p>',
                'en_title' => 'What\'s New',
                'en_content' => '
                    <h3 class="mb-3 text-xl font-bold">Latest Collections and Additions</h3>
                    <p class="mb-4 text-gray-600">The world of toys is constantly evolving, and at Bon Bon, we make sure to keep up with the latest trends! Discover the newest products we have recently added to our store.</p>
                    <p class="mb-4 text-gray-600">From interactive AI toys and modern educational games to the most popular cartoon characters released this year, we bring innovative entertainment to your doorstep.</p>
                    <p class="mb-4 text-gray-600 font-bold">Check this page frequently and do not forget to subscribe to our newsletter to stay updated on exclusive offers and new product launches.</p>',
            ],
            8 => [
                'url_key' => 'payment-policy',
                'ar_title' => 'سياسة الدفع',
                'ar_content' => '
                    <h3 class="mb-3 text-xl font-bold">طرق دفع متعددة وآمنة</h3>
                    <p class="mb-4 text-gray-600">نحن في بون بون نضع أمان بياناتكم المالية على رأس أولوياتنا. جميع عمليات الدفع التي تتم عبر الموقع مشفرة بأحدث التقنيات (SSL) لضمان بيئة دفع آمنة بنسبة 100%.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">الخيارات المتاحة:</h3>
                    <ul class="list-disc pl-5 mb-4 text-gray-600 space-y-2">
                        <li><strong>البطاقات الائتمانية والبنكية:</strong> نقبل بطاقات مدى، فيزا، وماستركارد.</li>
                        <li><strong>الدفع الإلكتروني السريع:</strong> خيارات الدفع عبر Apple Pay أو Google Pay لتجربة دفع أسرع.</li>
                        <li><strong>الدفع عند الاستلام:</strong> يتوفر هذا الخيار في مناطق محددة وقد يُضاف رسوم بسيطة لتغطية تكلفة الخدمة النقدية.</li>
                    </ul>
                    <p class="text-gray-600">لا يتم تخزين بيانات بطاقاتكم الائتمانية على خوادمنا نهائياً، بل يتم معالجتها مباشرة من خلال بوابات الدفع الموثوقة والمصرح لها.</p>',
                'en_title' => 'Payment Policy',
                'en_content' => '
                    <h3 class="mb-3 text-xl font-bold">Multiple and Secure Payment Methods</h3>
                    <p class="mb-4 text-gray-600">At Bon Bon, we prioritize the security of your financial data. All payment transactions on the site are encrypted using the latest (SSL) technology to ensure a 100% secure payment environment.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">Available Options:</h3>
                    <ul class="list-disc pl-5 mb-4 text-gray-600 space-y-2">
                        <li><strong>Credit and Debit Cards:</strong> We accept Mada, Visa, and Mastercard.</li>
                        <li><strong>Express Electronic Payment:</strong> Apple Pay or Google Pay options for a faster checkout experience.</li>
                        <li><strong>Cash on Delivery (COD):</strong> This option is available in select areas, and a small fee may be added to cover the cash handling service.</li>
                    </ul>
                    <p class="text-gray-600">Your credit card information is never stored on our servers; it is processed directly through trusted and authorized payment gateways.</p>',
            ],
            9 => [
                'url_key' => 'shipping-policy',
                'ar_title' => 'سياسة الشحن',
                'ar_content' => '
                    <h3 class="mb-3 text-xl font-bold">توصيل سريع وموثوق</h3>
                    <p class="mb-4 text-gray-600">ندرك أهمية وصول الألعاب في الوقت المناسب لإسعاد أطفالكم، لذلك قمنا بعقد شراكات مع أفضل شركات الشحن والتوصيل لضمان وصول طلباتكم بسرعة وأمان.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">مدة وتكلفة الشحن:</h3>
                    <ul class="list-disc pl-5 mb-4 text-gray-600 space-y-2">
                        <li><strong>الطلبات المحلية:</strong> تستغرق عادة من 1 إلى 3 أيام عمل.</li>
                        <li><strong>الطلبات الدولية أو للمناطق البعيدة:</strong> قد تستغرق من 3 إلى 7 أيام عمل حسب الوجهة.</li>
                        <li><strong>تكلفة الشحن:</strong> سيتم حساب التكلفة بناءً على الوزن والمنطقة خلال صفحة إتمام الطلب. (نوفر شحناً مجانياً للطلبات التي تتجاوز قيمة معينة).</li>
                    </ul>
                    <p class="text-gray-600">سيتلقى العميل رسالة نصية أو بريد إلكتروني برقم تتبع الشحنة فور خروجها من مستودعاتنا، لكي يتمكن من متابعة سيرها حتى تصل إليه.</p>',
                'en_title' => 'Shipping Policy',
                'en_content' => '
                    <h3 class="mb-3 text-xl font-bold">Fast and Reliable Delivery</h3>
                    <p class="mb-4 text-gray-600">We understand the importance of toys arriving on time to delight your children, so we have partnered with top shipping and courier companies to ensure your orders arrive quickly and safely.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">Shipping Duration and Cost:</h3>
                    <ul class="list-disc pl-5 mb-4 text-gray-600 space-y-2">
                        <li><strong>Local Orders:</strong> Typically takes 1 to 3 business days.</li>
                        <li><strong>International/Remote Area Orders:</strong> May take 3 to 7 business days depending on the destination.</li>
                        <li><strong>Shipping Cost:</strong> The cost will be calculated based on weight and location during checkout. (We offer free shipping for orders exceeding a certain value).</li>
                    </ul>
                    <p class="text-gray-600">The customer will receive an SMS or email with the shipment tracking number as soon as it leaves our warehouse, allowing them to track its progress until delivery.</p>',
            ],
            10 => [
                'url_key' => 'privacy-policy',
                'ar_title' => 'سياسة الخصوصية',
                'ar_content' => '
                    <h3 class="mb-3 text-xl font-bold">حماية خصوصيتكم</h3>
                    <p class="mb-4 text-gray-600">نلتزم في متجر بون بون بحماية خصوصيتكم بشكل كامل. توضح هذه السياسة كيف نقوم بجمع، واستخدام، وحماية بياناتكم الشخصية.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">جمع البيانات:</h3>
                    <p class="mb-4 text-gray-600">نقوم بجمع المعلومات الضرورية فقط لمعالجة طلباتكم، مثل الاسم، العنوان، رقم الهاتف، والبريد الإلكتروني. قد نستخدم ملفات تعريف الارتباط (Cookies) لتحسين تجربة التصفح الخاصة بكم وتقديم منتجات مقترحة تناسب اهتماماتكم.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">استخدام البيانات:</h3>
                    <p class="mb-4 text-gray-600">لا نقوم ببيع أو تأجير بياناتكم الشخصية لأي أطراف خارجية. يقتصر استخدام البيانات على تلبية الطلبات، التواصل معكم بشأن حالة الطلب، ولإرسال العروض الترويجية في حال موافقتكم المسبقة.</p>',
                'en_title' => 'Privacy Policy',
                'en_content' => '
                    <h3 class="mb-3 text-xl font-bold">Protecting Your Privacy</h3>
                    <p class="mb-4 text-gray-600">At Bon Bon store, we are fully committed to protecting your privacy. This policy explains how we collect, use, and safeguard your personal data.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">Data Collection:</h3>
                    <p class="mb-4 text-gray-600">We only collect the information necessary to process your orders, such as your name, address, phone number, and email. We may use cookies to improve your browsing experience and suggest products that match your interests.</p>
                    <h3 class="mb-3 text-xl font-bold mt-6">Use of Data:</h3>
                    <p class="mb-4 text-gray-600">We do not sell or rent your personal data to any third parties. Data usage is strictly limited to fulfilling orders, communicating with you about your order status, and sending promotional offers if you have explicitly opted in.</p>',
            ],
            11 => [
                'url_key' => 'contact-us',
                'ar_title' => 'اتصل بنا',
                'ar_content' => '
                    <h3 class="mb-3 text-xl font-bold">نبقى على تواصل</h3>
                    <p class="mb-4 text-gray-600">نسعد دائماً بالاستماع إلى عملائنا، سواء كان لديكم استفسار، اقتراح، أو حتى ملاحظة لتطوير خدماتنا.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div class="p-6 bg-gray-50 rounded-lg border border-gray-100">
                            <h4 class="font-bold text-lg mb-2">معلومات التواصل:</h4>
                            <ul class="text-gray-600 space-y-3">
                                <li><strong>البريد الإلكتروني:</strong> info@bonbon-kids.com</li>
                                <li><strong>رقم الهاتف:</strong> +966 XX XXX XXXX</li>
                                <li><strong>أوقات العمل:</strong> الأحد - الخميس، 9:00 صباحاً حتى 6:00 مساءً</li>
                            </ul>
                        </div>
                        <div class="p-6 bg-gray-50 rounded-lg border border-gray-100">
                            <h4 class="font-bold text-lg mb-2">موقعنا:</h4>
                            <p class="text-gray-600">تفضلوا بزيارة معارضنا للإطلاع على الألعاب بأنفسكم. يمكنكم العثور على فروعنا في الرياض وجدة، وسنقوم بافتتاح المزيد قريباً!</p>
                        </div>
                    </div>
                    <p class="mt-6 text-gray-600">يمكنكم أيضاً تعبئة نموذج المراسلة المتوفر في المتجر، وسيقوم أحد ممثلينا بالرد عليكم خلال 24 ساعة.</p>',
                'en_title' => 'Contact Us',
                'en_content' => '
                    <h3 class="mb-3 text-xl font-bold">Get In Touch</h3>
                    <p class="mb-4 text-gray-600">We are always happy to hear from our customers, whether you have an inquiry, a suggestion, or feedback to help us improve our services.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div class="p-6 bg-gray-50 rounded-lg border border-gray-100">
                            <h4 class="font-bold text-lg mb-2">Contact Information:</h4>
                            <ul class="text-gray-600 space-y-3">
                                <li><strong>Email:</strong> info@bonbon-kids.com</li>
                                <li><strong>Phone:</strong> +966 XX XXX XXXX</li>
                                <li><strong>Business Hours:</strong> Sunday - Thursday, 9:00 AM to 6:00 PM</li>
                            </ul>
                        </div>
                        <div class="p-6 bg-gray-50 rounded-lg border border-gray-100">
                            <h4 class="font-bold text-lg mb-2">Our Location:</h4>
                            <p class="text-gray-600">Visit our showrooms to see the toys yourself. You can find our branches in Riyadh and Jeddah, and we will be opening more soon!</p>
                        </div>
                    </div>
                    <p class="mt-6 text-gray-600">You can also fill out the contact form available on the store, and one of our representatives will reply within 24 hours.</p>',
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
