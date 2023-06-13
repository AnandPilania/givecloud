<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Illuminate\Support\Arr;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class GalleryShortcode extends Shortcode
{
    /**
     * Output the gallery template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $ids = $s->getParameter('ids', '');
        $style = $s->getParameter('style', '');

        $ids = explode(',', $ids);
        $ids = array_filter($ids, 'is_numeric');

        $media = \Ds\Models\Media::whereIn('id', $ids)
            ->get()
            ->filter(function ($media) {
                return $media->is_image;
            })->sortBy(function ($media) use ($ids) {
                return array_search($media->id, $ids);
            });

        $template = rtrim("templates/shortcodes/gallery.$style", '.');
        $template = new \Ds\Domain\Theming\Liquid\Template($template);

        return $template->render([
            'id' => uniqid('gallery-shortcode-'),
            'images' => $media,
            'parameters' => Arr::except($s->getParameters(), ['ids', 'style']),
        ]);
    }
}
