<?php

namespace Tests\Unit\Eloquent;

use Ds\Domain\Shared\Exceptions\PermissionException;
use Ds\Eloquent\Permissions;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    public function testUserCanOrRedirectThrowsPermissionExceptionWhenForbidden(): void
    {
        $this->expectException(PermissionException::class);

        /** @var \Ds\Eloquent\Permissions */
        $permissionsClass = new class {
            use Permissions;
        };
        $permissionsClass->userCanOrRedirect('forbidden-permission');
    }

    public function testUserCanOrRedirectReturnsTrueWhenAllowed(): void
    {
        /** @var \Ds\Eloquent\Permissions */
        $permissionsClass = new class {
            use Permissions;

            // Mock userCan() to always return true
            public function userCan($permissions, $all_must_be_true = false)
            {
                return true;
            }
        };

        $this->assertTrue($permissionsClass->userCanOrRedirect('allowed-permission'));
    }
}
