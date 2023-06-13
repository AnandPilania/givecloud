<?php

namespace Ds\Domain\Messenger\Console;

use Ds\Domain\Messenger\Support\Twilio;
use Illuminate\Console\Command;

class ProvisionPhoneNumberCommand extends Command
{
    /** @var string */
    protected $signature = 'messenger:provision-phone-number
                           {--toll-free : Look for toll-free phone numbers.}
                           {--country= : Country in which to search for phone numbers.}
                           {area-code? : Area code for local phone numbers.}';

    /** @var string */
    protected $description = 'Provisions first available phone number';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Twilio $twilio)
    {
        $tollFree = $this->option('toll-free');
        $countryCode = $this->option('country');
        $areaCode = $this->argument('area-code');

        if ($tollFree) {
            $phoneNumber = $twilio->searchTollFreePhoneNumbers($countryCode)->first();
        } else {
            if (empty($areaCode)) {
                $this->error('Area code is required for local phone numbers.');

                return 1;
            }

            $phoneNumber = $twilio->searchPhoneNumbers($areaCode, $countryCode)->first();
        }

        if (empty($phoneNumber)) {
            $this->error('No matching phone number was found.');

            return 1;
        }

        $twilio->provisionPhoneNumber($phoneNumber->phoneNumber);
    }
}
