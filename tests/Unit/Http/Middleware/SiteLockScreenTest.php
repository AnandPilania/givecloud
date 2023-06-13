<?php

namespace Tests\Unit\Http\Middleware;

use DomainException;
use Ds\Http\Middleware\SiteLockScreen;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\TestCase;

class SiteLockScreenTest extends TestCase
{
    public function testLockedSiteShowsLockScreen(): void
    {
        sys_set(['site_password' => 'one_password_to_rule_them_all']);

        $this->assertInstanceOf(
            View::class,
            $this->checkSiteLockScreenMiddleware('frontend.home')
        );
    }

    public function testLockedSiteDoesntShowLockScreenForExceptUri(): void
    {
        sys_set(['site_password' => 'one_password_to_rule_them_all']);

        $this->expectExceptionMessage('notShowingLockScreen');
        $this->checkSiteLockScreenMiddleware('backend.session.unlock_site');
    }

    public function testUnlockedSiteDoesntShowLockScreen(): void
    {
        $password = 'one_password_to_rule_them_all';

        session(['site_password' => $password]);
        sys_set(['site_password' => $password]);

        $this->expectExceptionMessage('notShowingLockScreen');
        $this->checkSiteLockScreenMiddleware('frontend.home');
    }

    public function testSiteThatHasNotBeenLockedDoesntShowLockScreen(): void
    {
        sys_set(['site_password' => null]);

        $this->expectExceptionMessage('notShowingLockScreen');
        $this->checkSiteLockScreenMiddleware('frontend.home');
    }

    private function checkSiteLockScreenMiddleware(string $routeName): View
    {
        $req = Request::create(route($routeName, null, false), Request::METHOD_GET);

        return $this->app->make(SiteLockScreen::class)->handle($req, function () {
            throw new DomainException('notShowingLockScreen');
        });
    }
}
