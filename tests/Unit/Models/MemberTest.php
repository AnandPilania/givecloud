<?php

namespace Tests\Unit\Models;

use Closure;
use Ds\Models\Email;
use Ds\Models\Member;
use Ds\Models\Member as Account;
use Ds\Models\MemberOptinLog;
use Ds\Models\Membership;
use Ds\Models\Order;
use Ds\Models\PromoCode;
use Mockery;
use Tests\TestCase;

/**
 * @group models
 * @group member
 */
class MemberTest extends TestCase
{
    public function testNotifyIncludesDefaultParams(): void
    {
        $emailMock = $this->createEmailMock(function ($merge) {
            return is_array($merge) && count($merge) > 1;
        });

        Member::factory()->make()->notify($emailMock);
    }

    public function testNotifyWithoutDefaultParams(): void
    {
        $emailMock = $this->createEmailMock(function ($merge) {
            return is_array($merge) && count($merge) === 0;
        });

        Member::factory()->make()->notify($emailMock, [], false);
    }

    public function testEmailOptInAccessorIsFalseForNewMember(): void
    {
        $member = Member::factory()->make();

        $this->assertFalse($member->email_opt_in);
    }

    public function testEmailOptInAccessor(): void
    {
        $optin = MemberOptinLog::factory()->optin()->make();
        $member = Member::factory()->create();
        $member->optinLogs()->save($optin);

        $this->assertTrue($member->email_opt_in);
        $this->assertEquals($optin->toArray(), $member->optinLogs->first()->toArray());
    }

    public function testEmailOptInMutator(): void
    {
        $member = Member::factory()->create();
        $this->assertFalse($member->email_opt_in);

        $member->email_opt_in = true;
        $member->save();

        $this->assertTrue($member->refresh()->email_opt_in);
    }

    public function testEmailOptOutAccessor(): void
    {
        $optin = MemberOptinLog::factory()->optout()->make();
        $member = Member::factory()->create();
        $member->optinLogs()->save($optin);

        $this->assertFalse($member->email_opt_in);
        $this->assertEquals($optin->toArray(), $member->optinLogs->first()->toArray());
    }

    public function testEmailOptOutMutator(): void
    {
        $member = Member::factory()->create();
        $member->optinLogs()->save(MemberOptinLog::factory()->optin()->make());
        $this->assertTrue($member->refresh()->email_opt_in);

        $member->email_opt_in = false;
        $member->save();

        $this->assertFalse($member->refresh()->email_opt_in);
    }

    public function testCanApplyMembershipPromoCodes()
    {
        $promo = PromoCode::factory()->isFreeShipping()->create();
        $membership = Membership::factory()->hasAttached($promo);

        $order = Order::factory()->create();
        $member = Account::factory()->hasAttached($membership, [], 'groups')->create();
        $member->applyMembershipPromocodes($order);

        $this->assertCount(1, $order->promoCodes);
        $this->assertContains($promo->code, $order->promoCodes->pluck('code')->all());
    }

    public function testApplyMembershipPromoCodesDoesNotRemoveOtherPromoCodes()
    {
        $promo = PromoCode::factory()->isFreeShipping()->create();
        $order = Order::factory()->hasAttached($promo)->create();
        $member = Account::factory()->create();

        $member->applyMembershipPromocodes($order);

        $this->assertCount(1, $order->promoCodes);
        $this->assertContains($promo->code, $order->promoCodes->pluck('code')->all());
    }

    public function testGetAvatarAttributeReturnsGravatarUrl(): void
    {
        $email = 'me@gmail.com';

        $member = Account::factory()->create([
            'email' => $email,
        ]);

        $this->assertSame(sprintf('https://www.gravatar.com/avatar/%s?d=404', md5($email)), $member->gravatar);
    }

    /** @dataProvider initialsDataProvider */
    public function testGetInitialsReturnsInitials($expected, $firstName, $lastName = null): void
    {
        $member = Account::factory()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);

        $this->assertSame($expected, $member->initials);
    }

    public function initialsDataProvider(): array
    {
        return [
            ['PP',  'Phil', 'Perusse'],
            ['HQ', 'Hydro-Quebec'],
            ['CD', 'Club des petits Dejeuners'],
            ['PA', 'Paul'],
            ['M', 'M'],
        ];
    }

    private function createEmailMock(Closure $with): Email
    {
        return tap(Mockery::mock(Email::class), function ($mock) use ($with) {
            $mock->shouldReceive('send')
                ->once()
                ->with(Mockery::any(), Mockery::on($with))
                ->andReturn(true);
        });
    }
}
