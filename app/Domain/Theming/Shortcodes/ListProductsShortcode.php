<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class ListProductsShortcode extends Shortcode
{
    /**
     * Output the products template.
     *
     * NOTE: compatibility wrapper for old Volt shortcode.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $product_codes = $s->getParameter('product_codes', '');
        $category_ids = $s->getParameter('category_ids', '');
        $order_by = $s->getParameter('order_by', '');
        $max_products = $s->getParameter('max_products', '');
        $show_summary = $s->getParameter('show_summary', '');

        return do_shortcode(sprintf(
            '[products style="grid" codes="%s" categories="%s" orderby="%s" limit="%s" summary="%s"]',
            $product_codes,
            $category_ids,
            $order_by,
            $max_products,
            $show_summary
        ));
    }
}
