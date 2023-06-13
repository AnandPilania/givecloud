<?php

namespace Ds\Services;

use BadMethodCallException;

class SiteService
{
    /**
     * Check if the site is protected.
     *
     * @return bool
     */
    public function isProtected(): bool
    {
        return (bool) sys_get('site_password');
    }

    /**
     * Check if the site is locked.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        if ($this->isProtected()) {
            return sys_get('site_password') !== session('site_password');
        }

        return false;
    }

    /**
     * Check if the site is suspended.
     *
     * @return bool
     */
    public function isSuspended(): bool
    {
        return (bool) sys_get('is_suspended');
    }

    /**
     * Check if this is a trial site.
     *
     * @return bool
     */
    public function isTrial(): bool
    {
        return (bool) site()->subscription->trial_ends_on;
    }

    /**
     * Check if this is an expired trial site.
     *
     * @return bool
     *
     * @throws \BadMethodCallException
     */
    public function trialHasExpired(): bool
    {
        if (! $this->isTrial()) {
            throw new BadMethodCallException('Expected a trial site');
        }

        return site()->subscription->trial_ends_on->isPast();
    }

    /**
     * Get the number of days remaining in the trial.
     *
     * @return int
     */
    public function getDaysRemainingInTrial(): int
    {
        if ($this->trialHasExpired()) {
            return 0;
        }

        return site()->subscription->trial_ends_on->diffInDays();
    }
}
