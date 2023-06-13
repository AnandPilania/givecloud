<?php

namespace Tests\Feature\Http\Middleware;

use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    public function testPromptTwoFactorAuth(): void
    {
        sys_set('two_factor_authentication', 'prompt');

        $res = $this->actingAsAdminUser()->get(route('backend.member.index'));

        $res->assertOk();
    }

    public function testForceTwoFactorAuth(): void
    {
        sys_set('two_factor_authentication', 'force');

        $res = $this->actingAsAdminUser()->get(route('backend.member.index'));

        $res->assertRedirect(route('backend.auth.2fa_nagger'));
    }

    public function testForceTwoFactorAuthWithSuperUser(): void
    {
        sys_set('two_factor_authentication', 'force');

        $res = $this->actingAsSuperUser()->get(route('backend.member.index'));

        $res->assertOk();
    }
}
