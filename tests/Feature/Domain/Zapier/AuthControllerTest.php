<?php

namespace Tests\Feature\Domain\Zapier;

/**
 * @group zapier
 */
class AuthControllerTest extends AbstractZapier
{
    public function testAuthCheck(): void
    {
        $this
            ->actingAsPassportUser(null, ['zapier'])
            ->getJson(route('zapier.auth.show'))
            ->assertOk()
            ->assertJson([]);
    }

    public function testAuthCheckForbiddenWhenMissingToken(): void
    {
        $this
            ->getJson(route('zapier.auth.show'))
            ->assertUnauthorized();
    }
}
