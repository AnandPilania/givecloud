<?php

namespace Tests\Unit\Domain\HotGlue\Listeners;

use Ds\Events\Event;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\Fakes\HotGlue\FakeHandler;
use Tests\TestCase;

/**
 * @group hotglue
 */
class AbstractHandlerTest extends TestCase
{
    public function testUrlReturnsStringBasedOnConfig(): void
    {
        Config::set('services.hotglue.test-target', [
            'flow_id' => 'my-target-flow-id',
        ]);

        $instance = $this->app->make(FakeHandler::class);

        $this->assertSame('my-target-flow-id/testing/jobs', $instance->url());
    }

    public function testHandleSendsStatePayloadToHotGlue(): void
    {
        Http::fake();

        Config::set('services.hotglue.test-target', [
            'flow_id' => 'my-target-flow-id',
        ]);

        $instance = $this->app->make(FakeHandler::class);

        $event = new class extends Event {
            public $foo = 'bar';
        };

        $instance->handle($event);

        Http::assertSent(function (Request $request) {
            return data_get($request, 'state.foo') === 'bar';
        });
    }
}
