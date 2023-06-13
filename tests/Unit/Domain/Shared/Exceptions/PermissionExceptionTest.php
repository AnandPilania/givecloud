<?php

namespace Tests\Unit\Domain\Shared\Exceptions;

use Ds\Domain\Shared\Exceptions\PermissionException;
use Tests\TestCase;

class PermissionExceptionTest extends TestCase
{
    public function testPermissionExceptionCanHandleMultipleExceptions(): void
    {
        $testPermissions = ['first-permission', 'second-permission'];

        $permissionException = new PermissionException($testPermissions);

        $this->assertSame($testPermissions, $permissionException->getPermissions());
    }

    public function testPermissionExceptionCanHandleSingleException(): void
    {
        $testPermission = 'first-permission';

        $permissionException = new PermissionException($testPermission);

        $this->assertSame([$testPermission], $permissionException->getPermissions());
    }
}
