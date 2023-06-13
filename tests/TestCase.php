<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use Concerns\InteractsWithAuthentication;
    use Concerns\InteractsWithPermissions;
    use Concerns\MakesArrayAssertions;
    use Concerns\MakesJsonAssertions;
    use CreatesApplication { CreatesApplication::tearDown as createsApplicationTearDown; }
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // minimize impact of compiled routes in leaky router instances
        if (file_exists($this->app->getCachedRoutesPath())) {
            try {
                $this->app['router']->setCompiledRoutes(['compiled' => [], 'attributes' => []]);
            } catch (\Mockery\Exception\BadMethodCallException $e) {
                // ignore mockery exception triggered when rebinding 'routes' in the container
            }
        }

        $this->createsApplicationTearDown();
    }
}
