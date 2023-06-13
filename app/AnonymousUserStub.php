<?php

namespace Ds;

use Ds\Domain\Shared\Exceptions\PermissionException;

class AnonymousUserStub
{
    /** @var int */
    public $id = null;

    /**
     * Checks permissions and redirects on failure.
     *
     * @param string|array $permissions
     * @return bool
     */
    public function canOrRedirect($permissions, $url = '/jpanel', $all_must_be_true = false)
    {
        throw new PermissionException($permissions, $url);
    }

    /**
     * Returns whether or not the user can do an action based on an array of permissions
     *
     * @return bool
     */
    public function can($permissions, $all_must_be_true = false)
    {
        return false;
    }

    /**
     * Return as blank string.
     */
    public function __toString()
    {
        return '';
    }
}
