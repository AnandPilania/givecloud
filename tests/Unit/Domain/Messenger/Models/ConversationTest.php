<?php

namespace Tests\Unit\Domain\Messenger\Models;

use BotMan\BotMan\Drivers\Tests\FakeDriver;
use Ds\Domain\Messenger\BotMan;
use Ds\Domain\Messenger\Models\Conversation;
use Ds\Domain\Messenger\Models\ConversationRecipient;
use Ds\Models\Member;
use Ds\Models\PaymentMethod;
use Ds\Models\Product;
use Ds\Models\Variant;
use Tests\Fakes\BotManTester;
use Tests\TestCase;

/**
 * @group t2g
 */
class ConversationTest extends TestCase
{
    public function testMultipleRegisterCommandsWithSameBeginningTriggersCorrectOne(): void
    {
        /** @var \Ds\Domain\Messenger\BotMan */
        $botManMock = $this->app->make(BotMan::class);
        $botManMock->setDriver($fakeDriver = FakeDriver::create());

        $accountPhoneNumber = $this->createCanadianAccountWithPaymentMethod()->bill_phone;
        $conversationsRecipients = ConversationRecipient::factory()->create([
            'identifier' => $accountPhoneNumber,
        ]);

        // Register `give {amount}` command.
        $conversationGive = $this->registerGiveCommand('give {amount}', 25);
        $conversationGive->recipients()->sync($conversationsRecipients);
        $conversationGive->registerCommand($botManMock);

        // Register `give xmas {amount}` command.
        $conversationGiveXmas = $this->registerGiveCommand('give xmas {amount}', 40);
        $conversationGiveXmas->recipients()->sync($conversationsRecipients);
        $conversationGiveXmas->registerCommand($botManMock);

        $botManTester = $this->createBotManTester($botManMock, $fakeDriver);

        sys_set('dcc_ai_is_enabled', false);

        // Test `give {amount}` TTG.
        $botManTester
            ->setUser(['id' => $accountPhoneNumber])
            ->setRecipient($accountPhoneNumber)
            ->receives('give 25')
            ->assertReply('Thank you for using Text-to-Give!')
            ->assertReply('Would you like to make this a monthly donation? Y or N')
            ->receives('Y')
            ->assertReply('Would you like to top up your donation by adding $1.43 to cover the processing fees? Y or N')
            ->receives('Y')
            ->assertReply("Thank you 🙂. A payment for $31.43 has been processed and we've emailed you a confirmation.");
        $this->assertStringStartsNotWith('give xmas', $botManTester->getBotManCommand()->getPattern());

        // Test `give xmas {amount}` TTG.
        $botManTester
            ->setUser(['id' => $accountPhoneNumber])
            ->setRecipient($accountPhoneNumber)
            ->receives('give xmas 40')
            ->assertReply('Thank you for using Text-to-Give!')
            ->assertReply('Would you like to make this a monthly donation? Y or N')
            ->receives('Y')
            ->assertReply('Would you like to top up your donation by adding $2.10 to cover the processing fees? Y or N')
            ->receives('N')
            ->assertReply("Thank you 🙂. A payment for $45.00 has been processed and we've emailed you a confirmation.");
        $this->assertStringStartsWith('give xmas', $botManTester->getBotManCommand()->getPattern());
    }

    private function createCanadianAccountWithPaymentMethod(): Member
    {
        $account = Member::factory()->canadian()->individual()->create();
        $account->paymentMethods()->saveMany([
            PaymentMethod::factory()->creditCard()->make(),
        ]);

        return $account;
    }

    private function createBotManTester(BotMan $botManMock, FakeDriver $fakeDriver): BotManTester
    {
        /* @var \Tests\Fakes\BotManTester */
        return $this->app->make(BotManTester::class, [
            'bot' => $botManMock,
            'driver' => $fakeDriver,
        ]);
    }

    private function registerGiveCommand(string $command, int $price): Conversation
    {
        /** @var \Ds\Domain\Messenger\Models\Conversation */
        $conversation = Conversation::factory()->create([
            'conversation_type' => 'donate_amount',
            'command' => $command,
        ]);
        $conversation->setMetadata('enable_monthly', true);
        $giveVariant = Variant::factory()->recurring()->create([
            'price' => $price,
        ]);
        $giveProduct = Product::factory()->create(['is_dcc_enabled' => true]);
        $giveProduct->variants()->saveMany([$giveVariant]);
        $conversation->setMetadata('product', $giveProduct->getKey());
        $conversation->save();

        return $conversation;
    }
}
