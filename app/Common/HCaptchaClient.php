<?php

namespace Ds\Common;

class HCaptchaClient extends ReCaptchaClient
{
    public const VERIFY_ENDPOINT_URL = 'https://hcaptcha.com/siteverify';

    /**
     * Check that the hostname matches a valid domain
     * for the site.
     *
     * @param string|null $hostname
     * @return bool
     */
    protected function checkHostname(?string $hostname): bool
    {
        return true;
    }

    /**
     * Returns the HTML to display the ReCaptcha form.
     */
    public function getHtml()
    {
        $output = '<div style="width:304px;margin:20px auto;">';
        $output .= '<div class="h-captcha" data-sitekey="' . $this->siteKey . '" data-id="' . self::$count . '"></div>';
        $output .= '<script src="https://hcaptcha.com/1/api.js" async defer></script>';
        $output .= '</div>';

        self::$count++;

        return $output;
    }
}
