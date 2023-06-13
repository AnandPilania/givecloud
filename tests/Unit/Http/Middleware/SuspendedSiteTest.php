<?php

namespace Tests\Unit\Http\Middleware;

use DomainException;
use Ds\Http\Middleware\SuspendedSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Tests\TestCase;

class SuspendedSiteTest extends TestCase
{
    public function testSuspendedSiteShowsSuspendedScreen(): void
    {
        sys_set(['is_suspended' => true]);

        $this->assertInstanceOf(
            View::class,
            $this->checkSuspendedSiteMiddleware('frontend.home')
        );
    }

    public function testSuspendedSiteDoesntShowSuspendedScreenForExceptUri(): void
    {
        sys_set(['is_suspended' => true]);

        $this->expectExceptionMessage('notShowingSuspendedScreen');
        $this->checkSuspendedSiteMiddleware('autologin', ['yrpaLVqw6']);
    }

    public function testSuspendedSiteDoesntShowSuspendedScreenForSuperUsers(): void
    {
        sys_set(['is_suspended' => true]);

        Auth::loginUsingId(config('givecloud.super_user_id'));

        $this->expectExceptionMessage('notShowingSuspendedScreen');
        $this->checkSuspendedSiteMiddleware('frontend.home');
    }

    public function testNonSuspendedSiteDoesntShowSuspendedScreen(): void
    {
        sys_set(['is_suspended' => false]);

        $this->expectExceptionMessage('notShowingSuspendedScreen');
        $this->checkSuspendedSiteMiddleware('frontend.home');
    }

    public function testActiveTrialDoesntShowSuspendedScreen(): void
    {
        site()->subscription->trial_ends_on = now()->addMonth();

        $this->expectExceptionMessage('notShowingSuspendedScreen');
        $this->checkSuspendedSiteMiddleware('frontend.home');
    }

    public function testExpiredTrialShowsSuspendedScreen(): void
    {
        site()->subscription->trial_ends_on = now()->subMonth();

        $this->assertInstanceOf(
            View::class,
            $this->checkSuspendedSiteMiddleware('frontend.home')
        );
    }

    private function checkSuspendedSiteMiddleware(string $routeName, array $parameters = null): View
    {
        $req = Request::create(route($routeName, $parameters, false), Request::METHOD_GET);

        return $this->app->make(SuspendedSite::class)->handle($req, function () {
            throw new DomainException('notShowingSuspendedScreen');
        });
    }
}
