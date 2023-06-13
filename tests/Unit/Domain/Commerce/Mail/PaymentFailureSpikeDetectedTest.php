<?php

namespace Tests\Unit\Domain\Commerce\Mail;

use Ds\Domain\Commerce\Mail\PaymentFailureSpikeDetected;
use Ds\Models\MonitoringIncident;
use Tests\Concerns\InteractsWithMailables;
use Tests\TestCase;

class PaymentFailureSpikeDetectedTest extends TestCase
{
    use InteractsWithMailables;

    public function testNoActionTaken(): void
    {
        $mailable = new PaymentFailureSpikeDetected(
            MonitoringIncident::factory()->create(),
            mt_rand(12, 950)
        );

        $this->assertMailablePreview($mailable);
    }
}
