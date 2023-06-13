<?php

namespace Ds\Http\Controllers\Frontend;

class SitemapsController extends Controller
{
    public function index()
    {
        $urls = [];

        $pages = \Ds\Models\Node::where('isActive', '=', '1')->where('type', '!=', 'menu')->securedByMembership()->get();

        foreach ($pages as $page) {
            if ($page->url) {
                $urls[] = (object) [
                    'loc' => $page->absUrl(true),
                    'lastmod' => toUtcFormat($page->updated_at, 'c'),
                    'changefreq' => 'monthly',
                    'priority' => 0.2,
                ];
            }
        }

        $categories = \Ds\Models\ProductCategory::isLocked()->get();

        foreach ($categories as $category) {
            $url = (object) [
                'loc' => $category->abs_url,
                'lastmod' => toUtcFormat('now', 'c'),
                'changefreq' => 'monthly',
                'priority' => 1.0,
            ];

            $urls[] = $url;
        }

        $products = \Ds\Models\Product::query()
            ->select('product.*')
            ->securedByMembership()
            ->active()
            ->groupBy('product.id')
            ->get();

        foreach ($products as $product) {
            $url = (object) [
                'loc' => $product->abs_url,
                'lastmod' => toUtcFormat('now', 'c'),
                'changefreq' => 'monthly',
                'priority' => 0.9,
            ];

            $urls[] = $url;
        }

        return response()
            ->view('frontend/sitemap', [
                'urls' => $urls,
            ])->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        return response()
            ->view('frontend/robots')
            ->header('Content-Type', 'text/plain');
    }
}
