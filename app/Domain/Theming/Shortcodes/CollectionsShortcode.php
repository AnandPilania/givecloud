<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class CollectionsShortcode extends Shortcode
{
    /**
     * Output the collections template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $style = $s->getParameter('style', '');
        $categories = $s->getParameter('categories', '');
        $limit = $s->getParameter('limit', '12');

        if (! $style) {
            return $this->error("'style' attribute required");
        }

        // cast the limit
        $limit = $limit ? min((int) $limit, 25) : 25;

        // category array
        $categories = collect(explode(',', $categories))
            ->map(function ($name) {
                return trim($name);
            })->reject(function ($name) {
                return empty($name);
            })->toArray();

        $cats = \Ds\Models\ProductCategory::query();

        if (count($categories) > 0) {
            $cats->whereIn('id', $categories);
        } else {
            $cats->topLevel();
        }

        if ($limit) {
            $cats->take($limit);
        }

        $template = rtrim("templates/shortcodes/collections.$style", '.');
        $template = new \Ds\Domain\Theming\Liquid\Template($template);

        return $template->render([
            'categories' => $cats->get(),
        ]);
    }
}
