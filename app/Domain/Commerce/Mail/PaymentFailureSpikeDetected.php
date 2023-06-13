<?php

namespace Ds\Domain\Commerce\Mail;

use Ds\Models\MonitoringIncident;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentFailureSpikeDetected extends Mailable
{
    use SerializesModels;
    use Queueable;

    /** @var array */
    protected $data;

    /**
     * Create a new message instance.
     *
     * @param \Ds\Models\MonitoringIncident $incident
     * @param int $failedCount
     * @return void
     */
    public function __construct(MonitoringIncident $incident, $failedCount)
    {
        $this->data = [
            'failedCount' => $failedCount,
            'evaluationWindow' => sys_get('int:arm_evaluation_window'),
            'rateThreshold' => sys_get('double:arm_rate_threshold') * 100,
            'actionTaken' => $incident->action_taken,
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->view('mailables.payment-failure-spike-detected', $this->data)
            ->subject('Payment Failure Spike Detected');
    }
}
