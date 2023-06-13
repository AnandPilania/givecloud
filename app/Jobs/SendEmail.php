<?php

namespace Ds\Jobs;

use Ds\Domain\MissionControl\MissionControlService;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Swift_Message;
use Throwable;

class SendEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /** @var \Swift_Message */
    protected $message;

    /**
     * Create a new job instance.
     *
     * @param \Swift_Message $message
     * @return void
     */
    public function __construct(Swift_Message $message)
    {
        $this->message = $this->modifyMessage($message);
    }

    /**
     * Execute the job.
     *
     * @param \Ds\Domain\MissionControl\MissionControlService $missionControlService
     * @return void
     */
    public function handle(MissionControlService $missionControlService)
    {
        $app = Container::getInstance();

        try {
            $app['swift.plugins.logger']->clear();

            // Force the transport to re-connect.
            // This will prevent errors in daemon queue situations.
            $app['mail.manager']->getSwiftMailer()->getTransport()->stop();

            if ($app->bound('events')) {
                $app['events']->dispatch(new MessageSending($this->message));
            }

            $sent = $app['mail.manager']->getSwiftMailer()->send($this->message);

            // Log the email in the support database
            $missionControlService->logSwiftMessage(
                $this->message,
                (string) $app['swift.plugins.logger']->dump(),
                (int) $sent
            );
        } catch (Throwable $e) {
            // Notify Bugsnag of the email failure
            if ($app->bound('bugsnag')) {
                $app['bugsnag']->notifyException($e, function ($report) use ($app) {
                    $report->setMetaData([
                        'message' => [
                            'to' => $this->message->getTo(),
                            'cc' => $this->message->getCc(),
                            'bcc' => $this->message->getBcc(),
                            'subject' => $this->message->getSubject(),
                        ],
                        'smtp_transaction' => $app['swift.plugins.logger']->dump(),
                    ]);
                });
            }

            // Release the job for another
            // attempt after a 5-minute delay
            if ($this->attempts() < $this->tries) {
                $this->release(300);
            } else {
                $this->fail($e);
            }
        }
    }

    /**
     * Modifies the message.
     *
     * - Converts existing "From" addresses to "Reply-To" addresses.
     * - Set the default "Reply-To" if there were no existing "From" addresses.
     * - Sets the "From" and "Sender" to the appropriate values.
     *
     * @param \Swift_Message $message
     * @return \Swift_Message
     */
    private function modifyMessage(Swift_Message $message)
    {
        $replyTo = $message->getFrom();

        // filter out the from email from the list of reply to emails
        foreach ($replyTo as $email => $name) {
            if (sys_get('email_from_address') === $email) {
                unset($replyTo[$email]);
            }
        }

        if (count($replyTo)) {
            $message->setReplyTo($replyTo);
        } else {
            if (sys_get('email_replyto_address')) {
                $message->setReplyTo(
                    sys_get('email_replyto_address'),
                    sys_get('email_from_name', sys_get('clientShortName'))
                );
            }
        }

        // set the from using the sites address
        $message->setFrom(
            sys_get('email_from_address'),
            sys_get('email_from_name', sys_get('clientShortName'))
        );

        // use our address as the sender
        if (sys_get('email_sender_required')) {
            $message->setSender('notifications@givecloud.co');
        }

        return $message;
    }
}
