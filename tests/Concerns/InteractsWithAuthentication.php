<?php

namespace Tests\Concerns;

use Ds\Models\Member;
use Ds\Models\User;
use Laravel\Passport\Passport;

trait InteractsWithAuthentication
{
    protected function actingAsAccount(?Member $account = null): self
    {
        $this->app['auth']->guard('account_web')->login($account ?: Member::factory()->create());

        return $this;
    }

    protected function actingAsUser(?User $user = null): self
    {
        $this->app['auth']->guard('web')->login($user ?: User::factory()->create());

        return $this;
    }

    protected function actingAsPassportUser(?User $user = null, array $scopes = ['*']): self
    {
        Passport::actingAs($user ?: User::factory()->create(), $scopes, 'passport');

        return $this;
    }

    protected function actingAsAdminUser(): self
    {
        $this->actingAsUser(User::factory()->admin()->create());

        return $this;
    }

    protected function actingAsSuperUser(): self
    {
        $this->actingAsUser(User::find(config('givecloud.super_user_id')));

        return $this;
    }
}
