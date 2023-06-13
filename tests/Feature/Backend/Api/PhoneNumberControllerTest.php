<?php

namespace Tests\Feature\Backend\Api;

use Ds\Domain\Messenger\Models\ConversationRecipient;
use Ds\Domain\Messenger\Support\Twilio;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Twilio\Exceptions\TwilioException;

/**
 * @group backend
 * @group api
 * @group t2g
 */
class PhoneNumberControllerTest extends TestCase
{
    public function testUserCannotProvisionAPhoneNumber(): void
    {
        $this->actingAsUser();
        $response = $this->postPhoneNumber('+145688556467');

        $response->assertForbidden();
        $response->assertJsonFragment(['error' => 'This action is unauthorized.']);
    }

    public function testSuperUserCanProvisionAPhoneNumber(): void
    {
        $phoneNumber = '+145688556467';

        $twilioMock = $this->createPartialMock(Twilio::class, ['createPhoneNumberOnTwilio']);
        $twilioMock
            ->expects($this->once())
            ->method('createPhoneNumberOnTwilio')
            ->willReturn((object) [
                'phoneNumber' => $phoneNumber,
                'sid' => 'sid',
            ]);
        $this->app->instance(Twilio::class, $twilioMock);

        $this->actingAsSuperUser();
        $response = $this->postPhoneNumber($phoneNumber);

        $response->assertOk();
        $response->assertExactJson([$phoneNumber]);
    }

    public function testSuperUserProvisionnigAPhoneNumberFails(): void
    {
        $phoneNumber = '+145688556467';

        $twilioMock = $this->createPartialMock(Twilio::class, ['createPhoneNumberOnTwilio']);
        $twilioMock
            ->expects($this->once())
            ->method('createPhoneNumberOnTwilio')
            ->willThrowException(new TwilioException());
        $this->app->instance(Twilio::class, $twilioMock);

        $this->actingAsSuperUser();
        $response = $this->postPhoneNumber($phoneNumber);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson(["An error occured when provisionning $phoneNumber"]);
    }

    public function testUserCannotreleaseAPhoneNumber(): void
    {
        $provisionedPhoneNumber = ConversationRecipient::factory()->twilio()->create();

        $this->actingAsUser();
        $response = $this->deletePhoneNumber($provisionedPhoneNumber);

        $response->assertForbidden();
        $response->assertJsonFragment(['error' => 'This action is unauthorized.']);
    }

    public function testSuperUserCanReleaseAPhoneNumber(): void
    {
        $provisionedPhoneNumber = ConversationRecipient::factory()->twilio()->create();

        $twilioMock = $this->createPartialMock(Twilio::class, ['deletePhoneNumberOnTwilio']);
        $twilioMock
            ->expects($this->once())
            ->method('deletePhoneNumberOnTwilio')
            ->willReturn(true);
        $this->app->instance(Twilio::class, $twilioMock);

        $this->actingAsSuperUser();
        $response = $this->deletePhoneNumber($provisionedPhoneNumber);

        $response->assertNoContent();
    }

    public function testSuperUserReleasingAPhoneNumberFailsWithoutTwilioNumber(): void
    {
        $provisionedPhoneNumber = ConversationRecipient::factory()->create();

        $this->actingAsSuperUser();
        $response = $this->deletePhoneNumber($provisionedPhoneNumber);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson(['Conversation recipient is not a Twilio phone number.']);
    }

    public function testSuperUserReleasingAPhoneNumberFails(): void
    {
        $provisionedPhoneNumber = ConversationRecipient::factory()->twilio()->create();

        $twilioMock = $this->createPartialMock(Twilio::class, ['deletePhoneNumberOnTwilio']);
        $twilioMock
            ->expects($this->once())
            ->method('deletePhoneNumberOnTwilio')
            ->willReturn(false);
        $this->app->instance(Twilio::class, $twilioMock);

        $this->actingAsSuperUser();
        $response = $this->deletePhoneNumber($provisionedPhoneNumber);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson(["An error occured when releasing $provisionedPhoneNumber->identifier"]);
    }

    public function testSuperUserReleasingAPhoneNumberThrowsTwilioException(): void
    {
        $provisionedPhoneNumber = ConversationRecipient::factory()->twilio()->create();

        $twilioMock = $this->createPartialMock(Twilio::class, ['deletePhoneNumberOnTwilio']);
        $twilioMock
            ->expects($this->once())
            ->method('deletePhoneNumberOnTwilio')
            ->willThrowException(new TwilioException);
        $this->app->instance(Twilio::class, $twilioMock);

        $this->actingAsSuperUser();
        $response = $this->deletePhoneNumber($provisionedPhoneNumber);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertExactJson(["An error occured when releasing $provisionedPhoneNumber->identifier"]);
    }

    private function deletePhoneNumber(ConversationRecipient $conversationRecipient): TestResponse
    {
        return $this->deleteJson(route('messenger.phone_numbers.destroy', $conversationRecipient));
    }

    private function postPhoneNumber(string $phoneNumber): TestResponse
    {
        return $this->postJson(
            route('messenger.phone_numbers.store'),
            ['phone_number' => $phoneNumber]
        );
    }
}
