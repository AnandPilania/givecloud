<?php

namespace Tests\Unit\Jobs\Orders;

use Ds\Jobs\Orders\SendSupporterContributionAcknowledgmentMail;
use Ds\Mail\SupporterContributionAcknowledgment;
use Ds\Models\Order;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendSupporterContributionAcknowledgmentMailTest extends TestCase
{
    public function testDoesNotSendWhenLegacyOrder(): void
    {
        Mail::fake();

        dispatch(new SendSupporterContributionAcknowledgmentMail(Order::factory()->create()));

        Mail::assertNotSent(SupporterContributionAcknowledgment::class);
    }
}
