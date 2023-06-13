<?php

namespace Tests\Unit\Domain\Webhook\Mail;

use Ds\Domain\Webhook\Mail\WebhookFailed;
use Ds\Models\HookDelivery;
use Tests\Concerns\InteractsWithMailables;
use Tests\TestCase;

class WebhookFailedTest extends TestCase
{
    use InteractsWithMailables;

    public function testMailablePreview(): void
    {
        $mailable = new WebhookFailed(
            HookDelivery::factory()->create(),
            'HTTP Error 500'
        );

        $this->assertMailablePreview($mailable);
    }
}
