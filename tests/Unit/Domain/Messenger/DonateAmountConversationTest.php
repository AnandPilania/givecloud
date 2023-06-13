<?php

namespace Tests\Unit\Domain\Messenger;

use BotMan\BotMan\Drivers\Tests\FakeDriver;
use Ds\Domain\Messenger\BotMan;
use Ds\Domain\Messenger\Conversation;
use Ds\Domain\Messenger\Conversations\DonateAmountConversation;
use Ds\Domain\Messenger\Models\Conversation as ConversationModel;
use Ds\Models\Member;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @group t2g
 */
class DonateAmountConversationTest extends TestCase
{
    public function testRunWhenSingleResumableConversationExceptionThrown(): void
    {
        $permalink = $this->fakeGenerateShortlink();
        $fakeDriver = $this->makeBotManDriver();

        $this->runConversationBot(
            $this->makeDonateAmountConversation(),
            $this->setupBotman($fakeDriver)
        );

        $replies = $fakeDriver->getBotMessages();
        $this->assertIsArray($replies);
        $this->assertCount(2, $replies);
        $this->assertSame('Thank you for using Text-to-Give!', $replies[0]->getText());
        $this->assertSame(
            "Welcome! Let's get you setup on Text-To-Give using this phone number. Tap the link to get started $permalink",
            $replies[1]->getText()
        );
    }

    public function testRunWhenMultipleResumableConversationExceptionsThrown(): void
    {
        $permalink = $this->fakeGenerateShortlink();
        $fakeDriver = $this->makeBotManDriver([
            'id' => Member::factory()->canadian()->create()->bill_phone_e164,
        ]);

        $this->runConversationBot(
            $this->makeDonateAmountConversation(),
            $this->setupBotman($fakeDriver)
        );

        $replies = $fakeDriver->getBotMessages();
        $this->assertIsArray($replies);
        $this->assertCount(3, $replies);
        $this->assertSame('Thank you for using Text-to-Give!', $replies[0]->getText());
        $this->assertSame(
            'Looks like you may not have a payment method setup or there may be a problem with your saved payment method.',
            $replies[1]->getText()
        );
        $this->assertSame(
            "Tap the link below to update your payment method. $permalink",
            $replies[2]->getText()
        );
    }

    private function fakeGenerateShortlink(string $shortlink = 'sms:hashid'): string
    {
        // Mocking url::routeAsShortlink() instead of reaching out to missioncontrol
        // to get the shortlink for the newly created ResumableConversation permalink.
        URL::shouldReceive('routeAsShortlink')
            ->once()
            ->andReturn($shortlink);

        return $shortlink;
    }

    private function makeBotManDriver(?array $user = []): FakeDriver
    {
        $fakeDriver = FakeDriver::create();
        $fakeDriver->setUser($user);
        // Generate a new name to avoid caching issue on BotMan::getUser() as it happen when there's no sender.
        $fakeDriver->setName('Fake' . Str::random(32));

        return $fakeDriver;
    }

    private function makeDonateAmountConversation(): DonateAmountConversation
    {
        /* @var \Ds\Domain\Messenger\Conversations\ReplyConversation */
        return $this->app->make(
            DonateAmountConversation::class,
            ['conversation' => ConversationModel::factory()->create()]
        );
    }

    private function runConversationBot(Conversation $conversation, $botManMock): void
    {
        $this->app->instance(BotMan::class, $botManMock);
        $conversation->setBot($botManMock);

        $conversation->run();
    }

    private function setupBotman(FakeDriver $driver): BotMan
    {
        $botManMock = $this->app->make(BotMan::class);
        $botManMock->setDriver($driver);

        return $botManMock;
    }
}
