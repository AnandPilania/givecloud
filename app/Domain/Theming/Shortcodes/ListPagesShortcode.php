<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class ListPagesShortcode extends Shortcode
{
    /**
     * List pages.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        ob_start();

        listMenu([
            'levels' => (string) $s->getParameter('levels'),
            'parentid' => (string) $s->getParameter('parent_id'),
            'siblingid' => (string) $s->getParameter('sibling_id'),
            'showoffline' => (string) $s->getParameter('show_offline'),
            'showhidden' => (string) $s->getParameter('show_hidden'),
        ]);

        return ob_get_clean();
    }
}
