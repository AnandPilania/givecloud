<?php

namespace Tests\Unit\Http\Middleware;

use Ds\Http\Middleware\AuthorizationRateMonitoring;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuthorizationRateMonitoringTest extends TestCase
{
    public function testStopPaymentsPeriodIsActive(): void
    {
        $until = now()->addMinutes(20)->toApiFormat();

        sys_set('public_payments_disabled', true);
        sys_set('public_payments_disabled_until', $until);

        $this->checkAuthorizationRateMonitoringMiddleware();

        $this->assertSame('1', sys_get('public_payments_disabled'));
        $this->assertSame($until, sys_get('public_payments_disabled_until'));
    }

    public function testStopPaymentsPeriodHasEnded(): void
    {
        sys_set('public_payments_disabled', true);
        sys_set('public_payments_disabled_until', now()->subMinutes(20)->toApiFormat());

        $this->checkAuthorizationRateMonitoringMiddleware();

        $this->assertSame('0', sys_get('public_payments_disabled'));
        $this->assertEmpty(sys_get('public_payments_disabled_until'));
    }

    private function checkAuthorizationRateMonitoringMiddleware(): void
    {
        $req = Request::create(route('frontend.home', null, false), Request::METHOD_GET);

        $this->app->make(AuthorizationRateMonitoring::class)->handle($req, function () {});
    }
}
