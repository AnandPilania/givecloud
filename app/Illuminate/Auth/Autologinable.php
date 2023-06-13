<?php

namespace Ds\Illuminate\Auth;

interface Autologinable
{
    /**
     * Handle an autologin.
     */
    public function autologin();

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey();

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass();

    /**
     * Get the default URL to use after an autologin
     * has occurred.
     *
     * @return string
     */
    public function getAutologinDefaultUrl();

    /**
     * Get an autologin URL.
     *
     * @param mixed $expires
     * @param string $path
     */
    public function getAutologinLink($expires = null, $path = null);
}
