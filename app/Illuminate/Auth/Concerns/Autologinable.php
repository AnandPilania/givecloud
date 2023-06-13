<?php

namespace Ds\Illuminate\Auth\Concerns;

use Ds\Models\AutologinToken;
use Illuminate\Support\Facades\Auth;

trait Autologinable
{
    /**
     * Handle an autologin.
     */
    public function autologin()
    {
        Auth::login($this);
    }

    /**
     * Get the default URL to use after an autologin
     * has occurred.
     *
     * @return string
     */
    public function getAutologinDefaultUrl()
    {
        return secure_site_url('jpanel');
    }

    /**
     * Get an autologin URL.
     *
     * @param mixed $expires
     * @param string $path
     */
    public function getAutologinLink($expires = null, $path = null)
    {
        $options = ['path' => $path];

        if ($expires === true) {
            $options['kamikaze'] = true;
        } else {
            $options['expires'] = $expires;
        }

        return AutologinToken::make($this, $options);
    }
}
