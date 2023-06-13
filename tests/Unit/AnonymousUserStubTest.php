<?php

namespace Tests\Unit;

use Ds\AnonymousUserStub;
use Ds\Domain\Shared\Exceptions\PermissionException;
use Tests\TestCase;

class AnonymousUserStubTest extends TestCase
{
    public function testCanOrRedirectThrowsPermissionException(): void
    {
        $this->expectException(PermissionException::class);

        $anonymousUserStub = new AnonymousUserStub();
        $anonymousUserStub->canOrRedirect('any-permission');
    }

    public function testCanReturnsFalse(): void
    {
        $anonymousUserStub = new AnonymousUserStub();

        $this->assertFalse($anonymousUserStub->can('any-permission'));
    }

    public function testToStringReturnsEmpty(): void
    {
        $this->assertEmpty((string) (new AnonymousUserStub()));
    }
}
