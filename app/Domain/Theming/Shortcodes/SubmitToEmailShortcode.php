<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class SubmitToEmailShortcode extends Shortcode
{
    /**
     * Output the email form template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $atts = [
            'name' => (string) $s->getParameter('name'),
            'email_to' => (string) $s->getParameter('email_to'),
            'email_cc' => (string) $s->getParameter('email_cc'),
            'email_bcc' => (string) $s->getParameter('email_bcc'),
            'success_url' => (string) $s->getParameter('success_url'),
            'fail_url' => (string) $s->getParameter('fail_url'),
            'captcha' => (string) $s->getParameter('captcha', 'true'),
        ];

        $name = e($atts['name']);
        $payload = base64_encode(encrypt($atts));
        $content = $s->getContent();

        return <<<HTML
<form name="$name" method="post" action="/ds/form/submit_to_email">
    <input name="payload" type="hidden" value="$payload">
    $content
</form>
HTML;
    }
}
