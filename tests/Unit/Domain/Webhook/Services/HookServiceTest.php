<?php

namespace Tests\Unit\Domain\Webhook\Services;

use Ds\Domain\Shared\DateTime;
use Ds\Domain\Webhook\Services\HookService;
use Ds\Domain\Webhook\Transformers\OrderTransformer;
use Ds\Http\Resources\OrderResource;
use Ds\Models\Hook;
use Ds\Models\HookDelivery;
use Ds\Models\HookEvent;
use Ds\Models\Member;
use Ds\Models\Order;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Factory as HttpClientFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response as HttpClientResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection as Collection;
use Illuminate\Support\Facades\Http;
use League\Fractal\Resource\Collection as FractalCollection;
use Mockery;
use Tests\TestCase;

/**
 * @group backend
 * @group hooks
 */
class HookServiceTest extends TestCase
{
    public function testStoreWithEventsSuccess(): void
    {
        $hookData = Hook::factory()->make();
        $eventNames = HookEvent::getEnabledEvents()->random(2);

        /** @var \Ds\Domain\Webhook\Services\HookService */
        $hookService = $this->app->make(HookService::class);
        $storedHook = $hookService->storeWithEvents(
            $hookData->active,
            $hookData->content_type,
            $hookData->payload_url,
            $hookData->secret,
            $eventNames
        );

        $this->assertInstanceOf(Hook::class, $storedHook);
        $this->assertIsInt($storedHook->getKey());
        $this->assertNotEmpty($storedHook->secret);
        $this->assertSame($hookData->active, $storedHook->active);
        $this->assertSame($hookData->content_type, $storedHook->content_type);
        $this->assertSame($hookData->insecure_ssl, $storedHook->insecure_ssl);
        $this->assertSame($hookData->payload_url, $storedHook->payload_url);
        $this->assertEquals($eventNames, $storedHook->events->map->name);
    }

    public function testStoreWithoutEventsAndSecretSuccess(): void
    {
        $hookData = Hook::factory()->make();

        /** @var \Ds\Domain\Webhook\Services\HookService */
        $hookService = $this->app->make(HookService::class);
        $storedHook = $hookService->storeWithEvents(
            $hookData->active,
            $hookData->content_type,
            $hookData->payload_url,
            null,
            new Collection()
        );

        $this->assertInstanceOf(Hook::class, $storedHook);
        $this->assertIsInt($storedHook->getKey());
        $this->assertNotEmpty($storedHook->secret);
        $this->assertSame($hookData->active, $storedHook->active);
        $this->assertSame($hookData->content_type, $storedHook->content_type);
        $this->assertSame($hookData->insecure_ssl, $storedHook->insecure_ssl);
        $this->assertSame($hookData->payload_url, $storedHook->payload_url);
        $this->assertEmpty($storedHook->events->toArray());
    }

    public function testUpdateWithEventsSuccess(): void
    {
        $hook = Hook::factory()->create();
        $hookEvents = HookEvent::factory(3)->make();
        $hook->events()->saveMany($hookEvents);
        $hookData = Hook::factory()->make();

        /** @var \Ds\Domain\Webhook\Services\HookService */
        $hookService = $this->app->make(HookService::class);
        $hookService->updateWithEvents(
            $hook->refresh(),
            $hookData->active,
            $hookData->content_type,
            $hookData->payload_url,
            $hookData->secret,
            $hookEvents->map->name
        );

        $this->assertInstanceOf(Hook::class, $hook);
        $this->assertSame($hook->getKey(), $hook->getKey());
        $this->assertSame($hookData->active, $hook->active);
        $this->assertSame($hookData->content_type, $hook->content_type);
        $this->assertSame($hookData->insecure_ssl, $hook->insecure_ssl);
        $this->assertSame($hookData->payload_url, $hook->payload_url);
        $this->assertEquals($hookEvents->toArray(), $hook->events->toArray());
    }

    public function testUpdateWithEventsWithoutEventsSuccess(): void
    {
        $hook = Hook::factory()->create();
        $hookData = Hook::factory()->make();

        /** @var \Ds\Domain\Webhook\Services\HookService */
        $hookService = $this->app->make(HookService::class);
        $hookService->updateWithEvents(
            $hook,
            $hookData->active,
            $hookData->content_type,
            $hookData->payload_url,
            $hookData->secret
        );

        $this->assertInstanceOf(Hook::class, $hook);
        $this->assertSame($hook->getKey(), $hook->getKey());
        $this->assertSame($hookData->active, $hook->active);
        $this->assertSame($hookData->content_type, $hook->content_type);
        $this->assertSame($hookData->insecure_ssl, $hook->insecure_ssl);
        $this->assertSame($hookData->payload_url, $hook->payload_url);
        $this->assertEmpty($hook->events->toArray());
    }

    public function testUpdateInsecureSslSuccess(): void
    {
        $hook = Hook::factory()->create();

        /** @var \Ds\Domain\Webhook\Services\HookService */
        $hookService = $this->app->make(HookService::class);
        $updatedHook = $hookService->updateInsecureSSL($hook, true);

        $this->assertInstanceOf(Hook::class, $updatedHook);
        $this->assertTrue($hook->insecure_ssl);
    }

    public function testShouldDeliverTrueWhenHasActiveEvent(): void
    {
        $hookEvent = HookEvent::factory()->make();
        $hook = Hook::factory()->create();
        $hook->events()->saveMany([$hookEvent]);

        /** @var \Ds\Domain\Webhook\Services\HookService */
        $hookService = $this->app->make(HookService::class);

        $this->assertTrue($hookService->shouldDeliver($hookEvent->name));
    }

    public function testShouldDeliverFalseWhenNoActiveEvent(): void
    {
        $hookEvent = HookEvent::factory()->make();
        $hook = Hook::factory()->inactive()->create();
        $hook->events()->saveMany([$hookEvent]);

        /** @var \Ds\Domain\Webhook\Services\HookService */
        $hookService = $this->app->make(HookService::class);

        $this->assertFalse($hookService->shouldDeliver($hookEvent->name));
    }

    public function testShouldDeliverFalseWhenNoMatchingEvent(): void
    {
        $hook = Hook::factory()->inactive()->create();
        $hook->events()->saveMany([HookEvent::factory()->make()]);

        /** @var \Ds\Domain\Webhook\Services\HookService */
        $hookService = $this->app->make(HookService::class);

        $this->assertFalse($hookService->shouldDeliver('unknown.event'));
    }

    public function testMakeDeliveries(): void
    {
        $order = $this->createOrder();
        $hookEvent = HookEvent::factory()->make();
        $hook = Hook::factory()->create();
        $hook->events()->saveMany([$hookEvent]);

        $orders = new FractalCollection([$order], new OrderTransformer, 'orders');
        $response = Http::response('body', HttpResponse::HTTP_OK, ['header' => 'sample']);

        Http::fake([$hook->payload_url => $response]);

        $this->app->make(HookService::class)->makeDeliveries($hookEvent->name, $orders);

        /** @var \Ds\Models\HookDelivery */
        $delivery = $hook->refresh()->deliveries->first();
        $this->assertDeliveryRequest($delivery, app('fractal')->createArray($orders));
        $this->assertDeliveryResponse($response, $delivery);
    }

    public function testMakeDeliveriesForJsonResource(): void
    {
        $order = Order::factory()->create();
        // Create the Member before the Hook to avoid it triggering an account_created hook
        $order->member()->associate(Member::factory()->individual()->create());
        $order->save();
        $resource = new OrderResource($order);
        $response = Http::response('body', HttpResponse::HTTP_OK, ['header' => 'sample']);

        $hookEvent = HookEvent::factory()->make();
        $hook = Hook::factory()->create();
        $hook->events()->saveMany([$hookEvent]);

        Http::fake([$hook->payload_url => $response]);

        $this->app->make(HookService::class)->makeDeliveries($hookEvent->name, $resource);

        /** @var \Ds\Models\HookDelivery */
        $delivery = $hook->refresh()->deliveries->first();
        $this->assertDeliveryRequest($delivery, $resource->toArray(new HttpRequest()));
        $this->assertDeliveryResponse($response, $delivery);
    }

    public function testMakeDeliveriesForUnsupportedPaylodThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unsupported resource type stdClass use as paylod in hook.');

        $hookEvent = HookEvent::factory()->make();
        Hook::factory()->create()
            ->events()
            ->saveMany([$hookEvent]);

        $this->app->make(HookService::class)->makeDeliveries($hookEvent->name, (object) ['incorrect payload']);
    }

    public function testMakeDeliveriesFailsWithRequestException(): void
    {
        $order = $this->createOrder();

        $hookEvent = HookEvent::factory()->make();
        $hook = Hook::factory()->create();
        $hook->events()->saveMany([$hookEvent]);
        $orders = new FractalCollection([$order], new OrderTransformer, 'orders');
        $response = Http::response('body', HttpResponse::HTTP_I_AM_A_TEAPOT, ['header' => 'sample']);

        Http::fake([$hook->payload_url => $response]);

        $this->app->make(HookService::class)->makeDeliveries($hookEvent->name, $orders);

        /** @var \Ds\Models\HookDelivery */
        $delivery = $hook->refresh()->deliveries->first();

        $this->assertDeliveryRequest($delivery, app('fractal')->createArray($orders));
        $this->assertDeliveryResponse($response, $delivery);
    }

    public function testRedeliver(): void
    {
        $hookEvent = HookEvent::factory()->make();
        $hook = Hook::factory()->create();
        $hook->events()->saveMany([$hookEvent]);
        $orders = new FractalCollection([Order::factory()->create()], new OrderTransformer, 'orders');
        $response = Http::response('body', HttpResponse::HTTP_OK, ['header' => 'sample']);

        Http::fake([$hook->payload_url => $response]);

        /** @var \Ds\Domain\Webhook\Services\HookService */
        $hookService = $this->app->make(HookService::class);
        $hookService->makeDeliveries($hookEvent->name, $orders);

        /** @var \Ds\Models\HookDelivery */
        $delivery = $hook->refresh()->deliveries->first();
        $hookService->redeliver($delivery);

        $this->assertDeliveryResponse($response, $delivery);
    }

    public function testMakeDeliveriesFailsWithExceptionWithoutResponse(): void
    {
        $order = $this->createOrder();

        $hookEvent = HookEvent::factory()->make();
        $hook = Hook::factory()->create();
        $hook->events()->saveMany([$hookEvent]);
        $orders = new FractalCollection([$order], new OrderTransformer, 'orders');
        $exception = new Exception('general exception message');

        $pendingRequest = Mockery::mock(PendingRequest::class, function ($mock) use ($exception) {
            $mock->shouldReceive('post')->once()->andThrow($exception);
        })->makePartial();

        $this->partialMock(HttpClientFactory::class, function ($mock) use ($pendingRequest) {
            $mock->shouldReceive('withOptions')->once()->andReturn($pendingRequest);
        });

        $this->app->make(HookService::class)->makeDeliveries($hookEvent->name, $orders);

        /** @var \Ds\Models\HookDelivery */
        $delivery = $hook->refresh()->deliveries->first();
        $this->assertDeliveryRequest($delivery, app('fractal')->createArray($orders));
        $this->assertSame(-1, $delivery->res_status);
        $this->assertEmpty($delivery->res_headers);
        $this->assertSame($exception->getMessage(), $delivery->res_body);
        $this->assertEmpty($delivery->completed_in);
    }

    protected function assertDeliveryRequest(HookDelivery $delivery, array $payload): void
    {
        $this->assertJson($delivery->req_body);
        $this->assertEquals(json_encode($payload), $delivery->req_body);
        $this->assertInstanceOf(DateTime::class, $delivery->delivered_at);
        $this->assertNotEmpty($delivery->guid);
        $this->assertNotEmpty($delivery->req_headers);
        $this->assertNotEmpty($delivery->req_body);
    }

    protected function assertDeliveryResponse(PromiseInterface $promise, HookDelivery $delivery): void
    {
        $response = new HttpClientResponse($promise->wait());

        $this->assertSame($response->getStatusCode(), $delivery->res_status);
        $this->assertSame($response->getHeadersAsString(), $delivery->res_headers);
        $this->assertSame((string) $response->getBody(), $delivery->res_body);
        $this->assertIsFloat($delivery->completed_in);
    }

    protected function createOrder(): Order
    {
        /** @var \Ds\Models\Order */
        $order = Order::factory()->create();

        // Create the Member before the Hook to avoid it triggering an account_created hook
        $order->member()->associate(Member::factory()->individual()->create());
        $order->save();

        return $order;
    }
}
