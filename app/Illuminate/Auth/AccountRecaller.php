<?php

namespace Ds\Illuminate\Auth;

use Illuminate\Auth\Recaller;

class AccountRecaller extends Recaller
{
    /**
     * Get the password from the recaller.
     *
     * @return string
     */
    public function hash()
    {
        return '';
    }

    /**
     * Determine if the recaller has all segments.
     *
     * @return bool
     */
    protected function hasAllSegments()
    {
        $segments = explode('|', $this->recaller);

        return count($segments) === 2 && trim($segments[0]) !== '' && trim($segments[1]) !== '';
    }
}
