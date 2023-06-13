<?php

namespace Tests\Feature\Backend\Api;

use Ds\Domain\Messenger\Models\ConversationRecipient;
use Ds\Domain\Messenger\Support\Twilio;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * @group backend
 * @group api
 * @group t2g
 */
class PhoneNumberSearchControllerTest extends TestCase
{
    public function testUserCannotSearchPhoneNumbers(): void
    {
        $this->actingAsUser();
        $response = $this->getJson(route('messenger.phone_numbers_search.index'));

        $response->assertForbidden();
    }

    public function testSuperUserSearchPhoneNumbersReturnsNoResults(): void
    {
        $twilioMock = $this->createPartialMock(Twilio::class, ['searchTollFreePhoneNumbers']);
        $twilioMock
            ->expects($this->once())
            ->method('searchTollFreePhoneNumbers')
            ->willReturn(new Collection);
        $this->app->instance(Twilio::class, $twilioMock);

        $this->actingAsSuperUser();
        $response = $this->getJson(route('messenger.phone_numbers_search.index', [
            'country' => 'CA',
            'type' => 'toll-free',
        ]));

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testSuperUserCanSearchTollFreePhoneNumbers(): void
    {
        $country = 'US';
        $this->localNumbers()->each(function ($localNumber) {
            ConversationRecipient::factory($this->localNumbers()->count())->create([
                'identifier' => '+1' . $localNumber->phoneNumber,
            ]);
        });
        $availablePhoneNumbers = ConversationRecipient::factory(5)
            ->twilio()
            ->tollFree()
            ->make()
            ->map(function ($recipient) {
                return (object) [
                    'phoneNumber' => $recipient->identifier,
                    'friendlyName' => $recipient->identifier_us_formatted,
                    'locality' => '',
                ];
            });

        $twilioMock = $this->createPartialMock(Twilio::class, ['searchTollFreePhoneNumbers']);
        $twilioMock
            ->expects($this->once())
            ->method('searchTollFreePhoneNumbers')
            ->with($country)
            ->willReturn($availablePhoneNumbers);
        $this->app->instance(Twilio::class, $twilioMock);

        $this->actingAsSuperUser();
        $response = $this->getJson(route('messenger.phone_numbers_search.index', [
            'country' => $country,
            'type' => 'toll-free',
        ]));

        $response->assertOk();
        $response->assertJsonCount($availablePhoneNumbers->count());
        $availablePhoneNumbers->each(function ($availablePhoneNumber) use ($response) {
            $response->assertJsonFragment([$availablePhoneNumber->phoneNumber]);
        });
    }

    public function testSuperUserCanSearchLocalPhoneNumbers(): void
    {
        $country = 'US';
        $areaCode = '303';
        ConversationRecipient::factory(3)->twilio()->tollFree()->create();
        $matchingPhoneNumbers = $this->localNumbers()->filter(function ($phoneNumber) use ($areaCode) {
            return substr($phoneNumber->phoneNumber, 0, 3) === $areaCode;
        });
        $this->localNumbers()->each(function ($localNumber) {
            return ConversationRecipient::factory()->create([
                'identifier' => '+1' . $localNumber->phoneNumber,
            ]);
        });

        $twilioMock = $this->createPartialMock(Twilio::class, ['searchPhoneNumbers']);
        $twilioMock
            ->expects($this->once())
            ->method('searchPhoneNumbers')
            ->with($areaCode, $country)
            ->willReturn($matchingPhoneNumbers);
        $this->app->instance(Twilio::class, $twilioMock);

        $this->actingAsSuperUser();
        $response = $this->getJson(route('messenger.phone_numbers_search.index', [
            'area_code' => $areaCode,
            'country' => $country,
            'type' => 'local',
        ]));

        $response->assertOk();
        $matchingPhoneNumbers->each(function ($availablePhoneNumber) use ($response) {
            $response->assertJsonFragment([$availablePhoneNumber->phoneNumber]);
        });
    }

    private function localNumbers(): Collection
    {
        return new Collection([
            (object) ['phoneNumber' => 3038970353, 'friendlyName' => '1 (303) 897-0353', 'locality' => ''],
            (object) ['phoneNumber' => 3104141891, 'friendlyName' => '1 (310) 414-1891', 'locality' => ''],
            (object) ['phoneNumber' => 6133116177, 'friendlyName' => '1 (613) 311-6177', 'locality' => ''],
            (object) ['phoneNumber' => 7126962835, 'friendlyName' => '1 (712) 696-2835', 'locality' => ''],
            (object) ['phoneNumber' => 8088249963, 'friendlyName' => '1 (808) 824-9963', 'locality' => ''],
        ]);
    }
}
