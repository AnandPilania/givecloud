<?php

namespace Ds\Domain\Messenger\Support;

use Ds\Domain\Messenger\Models\ConversationRecipient;
use Ds\Domain\Shared\Exceptions\MessageException;
use Illuminate\Support\Collection;
use stdClass;
use Twilio\Rest\Api\V2010\Account\AvailablePhoneNumberCountry\LocalInstance;
use Twilio\Rest\Api\V2010\Account\AvailablePhoneNumberCountry\TollFreeInstance;
use Twilio\Rest\Client;

class Twilio
{
    /** @var \Twilio\Rest\Client */
    private $client;

    /** @var \Twilio\Rest\Client */
    private $subaccountClient;

    /**
     * Search for available local phone numbers.
     * https://www.twilio.com/docs/phone-numbers/api/availablephonenumberlocal-resource
     *
     * @param string $areaCode
     * @param string $countryCode
     * @return \Illuminate\Support\Collection
     */
    public function searchPhoneNumbers($areaCode, $countryCode = null): Collection
    {
        if (empty($countryCode)) {
            $countryCode = sys_get('default_country');
        }

        $phoneNumbers = $this->getSubaccountClient()
            ->availablePhoneNumbers($countryCode)->local->read(['areaCode' => $areaCode], 20);

        return collect($phoneNumbers)
            ->filter(function (LocalInstance $phoneNumber) {
                return in_array($phoneNumber->addressRequirements, ['none', 'any']);
            })->filter(function (LocalInstance $phoneNumber) {
                return $phoneNumber->capabilities['SMS'] === true;
            })->map(function (LocalInstance $phoneNumber) {
                return (object) [
                    'phoneNumber' => $phoneNumber->phoneNumber,
                    'friendlyName' => $phoneNumber->friendlyName,
                    'locality' => $phoneNumber->locality,
                ];
            })->values();
    }

    /**
     * Search for available toll-free phone numbers.
     * https://www.twilio.com/docs/phone-numbers/api/availablephonenumber-tollfree-resource
     *
     * @param string $countryCode
     * @return \Illuminate\Support\Collection
     */
    public function searchTollFreePhoneNumbers($countryCode = null): Collection
    {
        if (empty($countryCode)) {
            $countryCode = sys_get('default_country');
        }

        $phoneNumbers = $this->getSubaccountClient()
            ->availablePhoneNumbers($countryCode)->tollFree->read([], 20);

        return collect($phoneNumbers)
            ->filter(function (TollFreeInstance $phoneNumber) {
                return in_array($phoneNumber->addressRequirements, ['none', 'any']);
            })->filter(function (TollFreeInstance $phoneNumber) {
                return $phoneNumber->capabilities['SMS'] === true;
            })->map(function (TollFreeInstance $phoneNumber) {
                return (object) [
                    'phoneNumber' => $phoneNumber->phoneNumber,
                    'friendlyName' => $phoneNumber->friendlyName,
                    'locality' => $phoneNumber->locality,
                ];
            })->values();
    }

    /**
     * Provision a phone number.
     * https://www.twilio.com/docs/phone-numbers/api/incomingphonenumber-resource
     *
     * @throws \Twilio\Exceptions\TwilioException — When an HTTP error occurs
     * @throws \Throwable
     */
    public function provisionPhoneNumber(string $phoneNumber): ConversationRecipient
    {
        $incomingPhoneNumber = $this->createPhoneNumberOnTwilio($phoneNumber);

        $recipient = new ConversationRecipient;
        $recipient->identifier = $incomingPhoneNumber->phoneNumber;
        $recipient->resource_type = 'phone_number';
        $recipient->twilio_sid = $incomingPhoneNumber->sid;
        $recipient->saveOrFail();

        return $recipient;
    }

    /**
     * https://www.twilio.com/docs/phone-numbers/api/incomingphonenumber-resource
     *
     * @throws \Twilio\Exceptions\TwilioException — When an HTTP error occurs
     * @throws \Exception
     */
    public function releasePhoneNumber(ConversationRecipient $recipient): bool
    {
        if (empty($recipient->twilio_sid)) {
            throw new MessageException('Conversation recipient is not a Twilio phone number.');
        }

        return $this->deletePhoneNumberOnTwilio($recipient->twilio_sid)
            && $recipient->delete();
    }

    /**
     * Create subaccount for Site.
     */
    private function createSubaccount()
    {
        $account = $this->getClient()->api->v2010->accounts->create([
            'friendlyName' => site('ds_account_name'),
        ]);

        if (empty($account) || $account->status !== 'active') {
            throw new MessageException('Problem creating Twilio subaccount, please contact Givecloud support to resolve.');
        }

        sys_set([
            'twilio_subaccount_sid' => $account->sid,
            'twilio_subaccount_token' => $account->authToken,
        ]);
    }

    /**
     * Create subaccount for Site.
     */
    public function closeSubaccount($sid)
    {
        $this->getClient()->api->v2010->accounts($sid)->update(['status' => 'closed']);
    }

    /**
     * @throws \Twilio\Exceptions\TwilioException — When an HTTP error occurs
     */
    protected function createPhoneNumberOnTwilio(string $phoneNumber): stdClass
    {
        $incomingPhoneNumberInstance = $this->getSubaccountClient()->incomingPhoneNumbers->create([
            'phoneNumber' => $phoneNumber,
            'smsUrl' => secure_site_url('webhook/messenger', true),
        ]);

        return (object) [
            'phoneNumber' => $incomingPhoneNumberInstance->phoneNumber,
            'sid' => $incomingPhoneNumberInstance->sid,
        ];
    }

    /**
     * @throws \Twilio\Exceptions\TwilioException — When an HTTP error occurs
     */
    protected function deletePhoneNumberOnTwilio(string $recipientTwilioSid): bool
    {
        return $this->getSubaccountClient()->incomingPhoneNumbers($recipientTwilioSid)->delete();
    }

    /**
     * @return \Twilio\Rest\Client
     */
    private function getClient(): Client
    {
        if (! $this->client) {
            $this->client = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );
        }

        return $this->client;
    }

    /**
     * @return \Twilio\Rest\Client
     */
    private function getSubaccountClient(): Client
    {
        if (! sys_get('twilio_subaccount_sid')) {
            $this->createSubaccount();
        }

        if (! $this->subaccountClient) {
            $this->subaccountClient = new Client(
                sys_get('twilio_subaccount_sid'),
                sys_get('twilio_subaccount_token')
            );
        }

        return $this->subaccountClient;
    }
}
