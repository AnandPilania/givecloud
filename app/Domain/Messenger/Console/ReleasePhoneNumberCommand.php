<?php

namespace Ds\Domain\Messenger\Console;

use Ds\Domain\Messenger\Models\ConversationRecipient;
use Ds\Domain\Messenger\Support\Twilio;
use Illuminate\Console\Command;

class ReleasePhoneNumberCommand extends Command
{
    /** @var string */
    protected $signature = 'messenger:release-phone-number
                           {recipient : Conversation recipient id.}';

    /** @var string */
    protected $description = 'Release a provisioned phone number';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Twilio $twilio)
    {
        $recipient = ConversationRecipient::find($this->argument('recipient'));

        if (empty($recipient)) {
            $this->error('Conversation recipient not found.');

            return 1;
        }

        return (int) $twilio->releasePhoneNumber($recipient);
    }
}
