<?php

namespace Tests\Unit\Domain\DoubleTheDonation;

use Ds\Domain\DoubleTheDonation\DoubleTheDonationService;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Order;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/** @group DoubleTheDonation */
class DoubleTheDonationServiceTest extends TestCase
{
    public function testTestThrowsExceptionWithNote(): void
    {
        Http::fake(fn () => Http::response(['note' => 'Error message in note'], 400));

        $this->expectException(MessageException::class);
        $this->expectErrorMessage('Error message in note');

        $this->app->make(DoubleTheDonationService::class)->test();
    }

    public function testTestThrowsExceptionWithErrorMessageIfNoNote(): void
    {
        Http::fake(fn () => Http::response(['no-note' => 'some other'], 400));

        $this->expectException(MessageException::class);
        $this->expectErrorMessage('An error occurred, please try again.');

        $this->app->make(DoubleTheDonationService::class)->test();
    }

    public function testTestThrowsExceptionWhenSomeKeyIsInvalid(): void
    {
        Http::fake(fn () => Http::response([
            'public_key_valid' => false,
            'private_key_valid' => true,
            'private_key_enabled' => true,
            'subscription_status' => 'active',
        ]));

        $this->expectException(MessageException::class);
        $this->expectErrorMessage('An error occurred, please try again.');

        $this->app->make(DoubleTheDonationService::class)->test();
    }

    public function testTestReturnsTrueWhenValid(): void
    {
        Http::fake(fn () => Http::response([
            'public_key_valid' => true,
            'private_key_valid' => true,
            'private_key_enabled' => true,
            'subscription_status' => 'active',
        ]));

        $response = $this->app->make(DoubleTheDonationService::class)->test();

        Http::assertSent(function (Request $request) {
            return Str::startsWith($request->url(), 'https://doublethedonation.com/api/360matchpro-partners/v1/verify-360-keys');
        });

        $this->assertTrue($response);
    }

    public function testRegisterOrderThrowsErrorIfOrderIsUnPaid(): void
    {
        $this->expectException(MessageException::class);
        $this->expectErrorMessage('Order must be paid.');

        $this->app->make(DoubleTheDonationService::class)->registerOrder(Order::factory()->create());
    }

    public function testRegisterOrderThrowsErrorIfPayloadIsInvalid(): void
    {
        Http::fake(fn () => Http::response('Malformed API Call', 401));

        $this->expectException(MessageException::class);
        $this->expectErrorMessage('Malformed API Call');

        $this->app->make(DoubleTheDonationService::class)->registerOrder(Order::factory()->paid()->create());
    }

    public function testRegisterOrderGetsExistingRecordWhenExists(): void
    {
        Http::fake(fn () => Http::response(['duplicate?' => true], 201));

        $order = Order::factory()->paid()->create();
        $this->partialMock(DoubleTheDonationService::class)->shouldReceive('getDonationRecord')->with($order->getKey())->andReturn([]);

        $this->app->make(DoubleTheDonationService::class)->registerOrder($order);
    }

    public function testRegisterOrderCanRegisterOrder(): void
    {
        Http::fake(fn () => Http::response([], 201));

        $order = Order::factory()->paid()->create();
        $this->app->make(DoubleTheDonationService::class)->registerOrder($order);

        Http::assertSent(function (Request $request) {
            return Str::startsWith($request->url(), 'https://doublethedonation.com/api/360matchpro-partners/v1/register_donation');
        });
    }
}
