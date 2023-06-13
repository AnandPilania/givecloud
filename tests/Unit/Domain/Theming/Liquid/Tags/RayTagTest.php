<?php

namespace Tests\Unit\Domain\Theming\Liquid\Tags;

use Mockery;
use Spatie\LaravelRay\Ray;
use Spatie\Ray\Client as RayClient;
use Spatie\Ray\Settings\Settings as RaySettings;
use Tests\TestCase;

class RayTagTest extends TestCase
{
    public function testWithMethodName(): void
    {
        $this->partialMock(Ray::class, function ($mock) {
            $mock->shouldReceive('clearScreen')->once();
        });

        liquid('{% ray "clearScreen" %}');
    }

    public function testWithVariable(): void
    {
        $this->instance(
            Ray::class,
            Mockery::mock(
                Ray::class,
                [$this->app->make(RaySettings::class), $this->app->make(RayClient::class)],
                function ($mock) {
                    $mock->shouldReceive('sendRequest')->once();
                }
            )->makePartial()
        );

        liquid('{% ray companyName %}', ['companyName' => 'Givecloud']);
    }
}
