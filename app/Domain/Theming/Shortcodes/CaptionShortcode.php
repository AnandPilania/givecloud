<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Liquid\Template;
use Ds\Domain\Theming\Shortcode;
use Illuminate\Support\Str;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class CaptionShortcode extends Shortcode
{
    /**
     * Output the posts template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $id = $s->getParameter('id', uniqid('caption-shortcode-'));
        $captionId = $s->getParameter('caption_id', '');
        $align = $s->getParameter('align', 'alignnone');
        $width = (int) $s->getParameter('width', '');
        $caption = $s->getParameter('caption', '');
        $class = $s->getParameter('class', '');

        $content = $s->getContent();

        // New-style shortcode with the caption inside the shortcode with the link and image tags.
        // @see: https://github.com/WordPress/WordPress/blob/537fd931bc02e6e934a2d774422b897871aa87ad/wp-includes/media.php#L1991-L1999
        if (empty($caption)) {
            if (preg_match('#((?:<a [^>]+>\s*)?<img [^>]+>(?:\s*</a>)?)(.*)#is', $content, $matches)) {
                $content = $matches[1];
                $caption = trim($matches[2]);
            }
        }

        if ($width < 1 || empty($caption)) {
            return $content;
        }

        $id = Str::slug($id);
        $captionId = $captionId ? Str::slug($captionId) : "caption-$id";

        $template = new Template('templates/shortcodes/caption');

        return $template->render([
            'id' => $id,
            'class' => trim("gc-caption $align $class"),
            'width' => $width,
            'content' => do_shortcode($content),
            'caption_id' => $captionId,
            'caption' => $caption,
        ]);
    }
}
