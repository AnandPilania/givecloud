<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class ProductsShortcode extends Shortcode
{
    /**
     * Output the products template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $style = $s->getParameter('style', '');
        $categories = $s->getParameter('categories', '');
        $codes = $s->getParameter('codes', '');
        $featured = $s->getParameter('featured', '');
        $orderby = $s->getParameter('orderby', '');
        $order = $s->getParameter('order', 'desc');
        $limit = $s->getParameter('limit', '6');
        $summary = $s->getParameter('summary', '');
        $matchheight = $s->getParameter('matchheight', '');

        if (! $style) {
            return $this->error("'style' attribute required");
        }

        // category array
        $categories = collect(explode(',', $categories))
            ->map(function ($name) {
                return trim($name);
            })->reject(function ($name) {
                return empty($name);
            })->toArray();

        // codes array
        $codes = collect(explode(',', $codes))
            ->map(function ($name) {
                return trim($name);
            })->reject(function ($name) {
                return empty($name);
            })->toArray();

        // only allow orderby: name, published_at or random
        if (! in_array($orderby, ['name', 'published_at', 'random'])) {
            if ($codes) {
                $orderby = 'natural';
            } else {
                $orderby = 'published_at';
            }
        }

        // normalize published_at to database column name
        if ($orderby === 'published_at') {
            $orderby = 'publish_start_date';
        }

        // only allow order: asc or desc
        if (! in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }

        // cast the limit
        $limit = $limit ? min((int) $limit, 80) : 80;

        $products = \Ds\Models\Product::active();

        if ($featured) {
            $products->orderBy('isfeatured', 'desc');
        }

        if ($orderby == 'random') {
            $products->orderBy(DB::raw('RAND()'));
        } elseif ($orderby !== 'natural') {
            $products->orderBy($orderby, $order);
        }

        if (count($categories) > 0) {
            $products->whereHas('categories', function ($qry) use ($categories) {
                $qry->whereIn('categoryid', $categories);
            });
        }

        if (count($codes) > 0) {
            $products->whereIn('code', $codes);
        }

        if ($limit) {
            $products->take($limit);
        }

        $template = rtrim("templates/shortcodes/products.$style", '.');
        $template = new \Ds\Domain\Theming\Liquid\Template($template);

        $products = $products->get();

        if ($orderby === 'natural') {
            $products = $products->sortBy(function ($product) use ($codes) {
                return array_search($product->code, $codes);
            });
        }

        return $template->render([
            'products' => $products,
            'match_height' => Str::boolify($matchheight),
            'show_summary' => Str::boolify($summary),
        ]);
    }
}
