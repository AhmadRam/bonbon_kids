<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\CMS\Repositories\PageRepository;
use Webkul\Product\Repositories\ProductRepository;

class SitemapController extends Controller
{
    /**
     * Cache TTL in seconds (1 day = 86400).
     */
    const CACHE_TTL = 86400;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected PageRepository $pageRepository
    ) {}

    /**
     * Return sitemap index XML.
     */
    public function index(): Response
    {
        $xml = Cache::remember('shop_sitemap_index_xml', self::CACHE_TTL, function () {
            $now = date('Y-m-d');

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

            $xml .= '  <sitemap>'."\n";
            $xml .= '    <loc>'.url('sitemap-categories.xml').'</loc>'."\n";
            $xml .= '    <lastmod>'.$now.'</lastmod>'."\n";
            $xml .= '  </sitemap>'."\n";

            $xml .= '  <sitemap>'."\n";
            $xml .= '    <loc>'.url('sitemap-products.xml').'</loc>'."\n";
            $xml .= '    <lastmod>'.$now.'</lastmod>'."\n";
            $xml .= '  </sitemap>'."\n";

            $xml .= '  <sitemap>'."\n";
            $xml .= '    <loc>'.url('sitemap-pages.xml').'</loc>'."\n";
            $xml .= '    <lastmod>'.$now.'</lastmod>'."\n";
            $xml .= '  </sitemap>'."\n";

            $xml .= '</sitemapindex>';

            return $xml;
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Return categories sitemap XML.
     */
    public function categories(): Response
    {
        $xml = Cache::remember('shop_sitemap_categories_xml', self::CACHE_TTL, function () {
            $categories = DB::table('category_translations as ct')
                ->join('categories as c', 'c.id', '=', 'ct.category_id')
                ->where('c.status', 1)
                ->whereNotNull('ct.slug')
                ->where('ct.slug', '!=', '')
                ->where('ct.slug', '!=', 'root')
                ->select('ct.slug', DB::raw('MAX(c.updated_at) as updated_at'))
                ->groupBy('ct.slug')
                ->get();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

            foreach ($categories as $category) {
                $loc = htmlspecialchars(url($category->slug), ENT_XML1, 'UTF-8');
                $lastmod = $category->updated_at ? date('Y-m-d', strtotime($category->updated_at)) : date('Y-m-d');

                $xml .= '  <url>'."\n";
                $xml .= '    <loc>'.$loc.'</loc>'."\n";
                $xml .= '    <lastmod>'.$lastmod.'</lastmod>'."\n";
                $xml .= '  </url>'."\n";
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Return products sitemap XML.
     */
    public function products(): Response
    {
        $xml = Cache::remember('shop_sitemap_products_xml', self::CACHE_TTL, function () {
            $products = DB::table('product_flat')
                ->where('status', 1)
                ->where('visible_individually', 1)
                ->whereNotNull('url_key')
                ->where('url_key', '!=', '')
                ->select('url_key', DB::raw('MAX(updated_at) as updated_at'))
                ->groupBy('url_key')
                ->get();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

            foreach ($products as $product) {
                $loc = htmlspecialchars(url($product->url_key), ENT_XML1, 'UTF-8');
                $lastmod = $product->updated_at ? date('Y-m-d', strtotime($product->updated_at)) : date('Y-m-d');

                $xml .= '  <url>'."\n";
                $xml .= '    <loc>'.$loc.'</loc>'."\n";
                $xml .= '    <lastmod>'.$lastmod.'</lastmod>'."\n";
                $xml .= '  </url>'."\n";
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Return CMS pages and homepage sitemap XML.
     */
    public function pages(): Response
    {
        $xml = Cache::remember('shop_sitemap_pages_xml', self::CACHE_TTL, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

            // Add Homepage
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.htmlspecialchars(url('/'), ENT_XML1, 'UTF-8').'</loc>'."\n";
            $xml .= '    <lastmod>'.date('Y-m-d').'</lastmod>'."\n";
            $xml .= '  </url>'."\n";

            // Add Contact Us
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.htmlspecialchars(route('shop.home.contact_us'), ENT_XML1, 'UTF-8').'</loc>'."\n";
            $xml .= '    <lastmod>'.date('Y-m-d').'</lastmod>'."\n";
            $xml .= '  </url>'."\n";

            $pages = DB::table('cms_page_translations as pt')
                ->join('cms_pages as p', 'p.id', '=', 'pt.cms_page_id')
                ->whereNotNull('pt.url_key')
                ->where('pt.url_key', '!=', '')
                ->select('pt.url_key', DB::raw('MAX(p.updated_at) as updated_at'))
                ->groupBy('pt.url_key')
                ->get();

            foreach ($pages as $page) {
                $loc = htmlspecialchars(route('shop.cms.page', $page->url_key), ENT_XML1, 'UTF-8');
                $lastmod = $page->updated_at ? date('Y-m-d', strtotime($page->updated_at)) : date('Y-m-d');

                $xml .= '  <url>'."\n";
                $xml .= '    <loc>'.$loc.'</loc>'."\n";
                $xml .= '    <lastmod>'.$lastmod.'</lastmod>'."\n";
                $xml .= '  </url>'."\n";
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Clear the sitemap caches.
     */
    public static function clearCache(): void
    {
        Cache::forget('shop_sitemap_index_xml');
        Cache::forget('shop_sitemap_categories_xml');
        Cache::forget('shop_sitemap_products_xml');
        Cache::forget('shop_sitemap_pages_xml');
    }
}
