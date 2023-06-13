<?php

namespace Ds\Common;

use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReCaptchaClient
{
    public const VERIFY_REQUEST_KEY = 'g-recaptcha-response';
    public const VERIFY_ENDPOINT_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /** @var int */
    protected static $count = 0;

    /** @var string */
    protected $siteKey;

    /** @var string */
    protected $secretKey;

    /** @var \Illuminate\Session\SessionManager */
    protected $session;

    /**
     * Create an instance.
     */
    public function __construct($siteKey, $secretKey, SessionManager $session)
    {
        $this->siteKey = $siteKey;
        $this->secretKey = $secretKey;
        $this->session = $session;
    }

    /**
     * Calls the reCAPTCHA siteverify API to verify whether the user passes CAPTCHA test.
     *
     * @return bool
     */
    public function verify()
    {
        // Skip verifying for Dusk requests but don't skip
        // for production or when running regular unit/feature tests
        if (app()->runningUnitTests() && ! app()->runningInConsole()) {
            return true;
        }

        $token = request(static::VERIFY_REQUEST_KEY);

        if (empty($token)) {
            return false;
        }

        if ($this->verificationTokenMatches($token)) {
            return true;
        }

        $response = Http::asForm()
            ->post(static::VERIFY_ENDPOINT_URL, [
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => request()->ip(),
            ])->object();

        if (isset($response->success) && $response->success == true) {
            return $this->checkHostname($response->hostname ?? null);
        }

        return false;
    }

    /**
     * Generate a verification token and store in the session.
     *
     * @return string
     */
    public function generateVerificationToken(): string
    {
        return tap(Str::random(40), function ($token) {
            $this->session->put('recaptcha-manual-token', $token);
        });
    }

    /**
     * Check if token matches a verification token in the session.
     *
     * @param string $token
     * @return bool
     */
    protected function verificationTokenMatches(string $token): bool
    {
        return $token === $this->session->get('recaptcha-manual-token');
    }

    /**
     * Check that the hostname matches a valid domain
     * for the site.
     *
     * @param string|null $hostname
     * @return bool
     */
    protected function checkHostname(?string $hostname): bool
    {
        // temporarily disabling hostname verification until we
        // have a chance to address for embedded fundraising forms

        /*
        $hostnames = array_merge(
            site()->subdomains,
            site()->domains->pluck('name')->all()
        );

        return in_array($hostname, $hostnames, true);
        */

        return true;
    }

    /**
     * Returns the HTML to display the ReCaptcha form.
     */
    public function getHtml()
    {
        $output = '<div style="width:304px;margin:20px auto;">';
        $output .= '<div class="g-recaptcha" data-sitekey="' . $this->siteKey . '" data-id="' . self::$count . '"></div>';
        $output .= '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=' . app()->getLocale() . '"></script>';
        $output .= '</div>';

        self::$count++;

        return $output;
    }
}
