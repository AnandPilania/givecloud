<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class SignupShortcode extends Shortcode
{
    /**
     * Output the signup template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $atts = [
            'name' => (string) $s->getParameter('name'),
            'group' => (string) $s->getParameter('group'),
            'success_url' => (string) $s->getParameter('success_url'),
            'success_message' => (string) $s->getParameter('success_message'),
            'fail_url' => (string) $s->getParameter('fail_url'),
            'fail_message' => (string) $s->getParameter('fail_message'),
            'captcha' => (string) $s->getParameter('captcha', 'true'),
        ];

        $name = e($atts['name']);
        $success_message = e($atts['success_message']);
        $fail_message = e($atts['fail_message']);
        $payload = base64_encode(encrypt($atts));
        $content = $s->getContent();

        return <<<HTML
<form name="$name" method="post" action="/ds/form/signup" data-success-message="$success_message" data-fail-message="$fail_message">
    <input name="payload" type="hidden" value="$payload">
    $content
</form>
HTML;
    }
}
