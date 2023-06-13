<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class LoggedInShortcode extends Shortcode
{
    /**
     * Output the logged in content.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $content = explode('[else]', $s->getContent());
        $logged_in_content = $content[0];
        $logged_out_content = (count($content) > 1) ? $content[1] : '';

        return (member()) ? $logged_in_content : $logged_out_content;
    }
}
