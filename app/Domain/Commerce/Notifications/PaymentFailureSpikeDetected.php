<?php

namespace Ds\Domain\Commerce\Notifications;

use Ds\Models\MonitoringIncident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class PaymentFailureSpikeDetected extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var array */
    protected $data;

    /** @var \Ds\Models\MonitoringIncident */
    protected $incident;

    /**
     * Create a new notification instance.
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

        $this->incident = $incident;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Ds\Domain\Commerce\Mail\PaymentFailureSpikeDetected
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->view('mailables.payment-failure-spike-detected', $this->data)
            ->subject('🆘 Payment Failure Spike Detected on ' . strtoupper(site()->ds_account_name));
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->error()
            ->content('🆘 Payment Failure Spike Detected on ' . strtoupper(site()->ds_account_name))
            ->attachment(function ($attachment) {
                $attachment->title('1. Check the Payments Dashboard', 'https://app.datadoghq.com/dashboard/ev7-ssp-rz4/payments');
            })->attachment(function ($attachment) {
                $attachment->title('2. Review Security Settings', url('jpanel/settings/security'))
                    ->content(sprintf(
                        'There has been %s payment failures over the last %s minutes.',
                        $this->data['failedCount'],
                        $this->data['evaluationWindow']
                    ));
            });
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->data;
    }
}
