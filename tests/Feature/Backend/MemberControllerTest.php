<?php

namespace Tests\Feature\Backend;

use Ds\Enums\ProductType;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Enums\Supporters\SupporterStatus;
use Ds\Models\Account;
use Ds\Models\FundraisingPage;
use Ds\Models\GroupAccount;
use Ds\Models\Member;
use Ds\Models\MemberLogin;
use Ds\Models\MemberOptinLog;
use Ds\Models\Membership;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\PaymentMethod;
use Ds\Models\Product;
use Ds\Models\UserDefinedField;
use Ds\Models\Variant;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

/**
 * @group backend
 */
class MemberControllerTest extends TestCase
{
    use InteractsWithRpps;

    public function testSaveWithoutUserDefinedFields()
    {
        $member = Member::factory()->create();

        $this->actingAsSuperUser()
            ->post(route('backend.member.save'), [$member->getKeyName() => $member->getKey()])
            ->assertRedirect(route('backend.member.edit', $member->refresh()));

        $this->assertEmpty($member->userDefinedFields->toArray());
    }

    public function testSaveWithUserDefinedFieldsMultipleChoice()
    {
        $member = Member::factory()->create();
        $userDefinedFields = UserDefinedField::factory(3)->multipleChoice()->create();
        $userDefinedFieldsSelected = $userDefinedFields->mapWithKeys(function ($udf) {
            return [$udf->getKey() => Arr::random(
                array_keys($udf->field_attributes['options']),
                mt_rand(1, count($udf->field_attributes['options']))
            )];
        });

        $this->actingAsSuperUser()
            ->post(route('backend.member.save'), [
                $member->getKeyName() => $member->getKey(),
                'user_defined_fields' => $userDefinedFieldsSelected->toArray(),
            ])->assertRedirect(route('backend.member.edit', $member->refresh()));

        $this->assertNotEmpty($member->userDefinedFields->toArray());
        $this->assertEquals(
            $userDefinedFieldsSelected,
            $member->userDefinedFields->mapWithKeys(function ($udf) {
                return [$udf->getKey() => $udf->pivot->value];
            })
        );
    }

    public function testSaveWithUserDefinedFieldsChoiceOrRadio()
    {
        $member = Member::factory()->create();
        $userDefinedFields = UserDefinedField::factory(3)->choice()->create()
            ->merge(UserDefinedField::factory(3)->radio()->create());
        $userDefinedFieldsSelected = $userDefinedFields->mapWithKeys(function ($udf) {
            return [$udf->getKey() => mt_rand(0, count($udf->field_attributes['options']) - 1)];
        });

        $this->actingAsSuperUser()
            ->post(route('backend.member.save'), [
                $member->getKeyName() => $member->getKey(),
                'user_defined_fields' => $userDefinedFieldsSelected->toArray(),
            ])->assertRedirect(route('backend.member.edit', $member->refresh()));

        $this->assertNotEmpty($member->userDefinedFields->toArray());
        $this->assertEquals(
            $userDefinedFieldsSelected,
            $member->userDefinedFields->mapWithKeys(function ($udf) {
                return [$udf->getKey() => $udf->pivot->value];
            })
        );
    }

    public function testSaveWithUserDefinedFieldsShortText()
    {
        $member = Member::factory()->create();
        $userDefinedFields = UserDefinedField::factory(3)->shortText()->create();
        $userDefinedFieldsSelected = $userDefinedFields->mapWithKeys(function ($udf) {
            return [$udf->getKey() => isset($udf->field_attributes['type']) && $udf->field_attributes['type'] === 'number'
                ? mt_rand(0, 100)
                : 'some text',
            ];
        });

        $this->actingAsSuperUser()
            ->post(route('backend.member.save'), [
                $member->getKeyName() => $member->getKey(),
                'user_defined_fields' => $userDefinedFieldsSelected->toArray(),
            ])->assertRedirect(route('backend.member.edit', $member->refresh()));

        $this->assertNotEmpty($member->userDefinedFields->toArray());
        $this->assertEquals(
            $userDefinedFieldsSelected,
            $member->userDefinedFields->mapWithKeys(function ($udf) {
                return [$udf->getKey() => $udf->pivot->value];
            })
        );
    }

    public function testSavingWithEmail()
    {
        $member = Member::factory()->create();

        $res = $this->actingAsSuperUser()
            ->post(route('backend.member.save'), [
                $member->getKeyName() => $member->getKey(),
                'email' => $member->email,
            ]);

        $res->assertSessionDoesntHaveErrors('email', 'The email is already in use by another account.');
    }

    public function testSavingWithEmailThatIsAlreadyInUse()
    {
        $member = Member::factory()->create();

        $res = $this->actingAsSuperUser()
            ->post(route('backend.member.save'), ['email' => $member->email]);

        $res->assertSessionHasErrors('email', 'The email is already in use by another supporter.');
    }

    public function testSavingWithoutEmail()
    {
        $member = Member::factory()->create();

        $res = $this->actingAsSuperUser()
            ->post(route('backend.member.save'), [
                $member->getKeyName() => $member->getKey(),
                'email' => null,
            ]);

        $res->assertSessionDoesntHaveErrors('email', 'The email is already in use by another supporter.');

        $this->assertNull($member->refresh()->email);
    }

    public function testMemberCanExportEmails()
    {
        $members = Member::factory(3)->create();

        $response = $this
            ->actingAsUser($this->createUserWithPermissions('member.'))
            ->get(route('backend.member.export_emails'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename=supporter_emails.csv');

        $csvFileContent = $response->streamedContent();
        foreach ($members as $member) {
            $this->assertStringContainsString($member->first_name, $csvFileContent);
            $this->assertStringContainsString($member->last_name, $csvFileContent);
            $this->assertStringContainsString($member->email, $csvFileContent);
            $this->assertStringContainsString($member->getShareableLink('/'), $csvFileContent);
        }
    }

    public function testMemberCanExportAll()
    {
        $memberships = Membership::factory(3)->create();
        $members = Member::factory(6)
            ->withLifetimeAggregatedValues()
            ->create()->each(function ($member) use ($memberships) {
                $member->groups()->attach(
                    $memberships
                        ->random()
                        ->take(mt_rand(0, 3))
                        ->get()
                        ->mapWithKeys(function ($membership) {
                            return [$membership->getKey() => GroupAccount::factory()->make()->getAttributes()];
                        })->toArray()
                );
            });

        $response = $this
            ->actingAsUser($this->createUserWithPermissions('member.'))
            ->get(route('backend.member.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename=supporters.csv');

        $csvFileContent = $response->streamedContent();
        foreach ($members as $member) {
            $this->assertMemberExported($member, $csvFileContent);
        }
    }

    public function testMemberCanExportAllBetweenDates(): void
    {
        sys_set('members_exports_chunk_size', 1);

        $membership = Membership::factory()->create();
        $members = Member::factory(6)
            ->withLifetimeAggregatedValues()
            ->hasAttached($membership, [], 'groups')
            ->create();

        $response = $this
            ->actingAsUser($this->createUserWithPermissions('member.'))
            ->get(route('backend.member.export', ['fm' => $membership->getKey()]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename=supporters.csv');

        $csvFileContent = $response->streamedContent();
        foreach ($members as $member) {
            $this->assertMemberExported($member, $csvFileContent);
        }
    }

    /**
     * @dataProvider datesFilterDataProvider
     */
    public function testCanFilterPaymentDates(array $paymentState, array $filters): void
    {
        Account::factory(5)->create();

        Account::factory()->state($paymentState)->create();

        $this
            ->actingAsUser($this->createUserWithPermissions('member.'))
            ->post(route('backend.member.listing'), $filters)
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function datesFilterDataProvider()
    {
        Carbon::setTestNow(); // Reset mock.

        $today = '2021-04-21';
        $yesterday = '2021-04-20';
        $twoDaysBehind = '2021-04-19';
        $lastMonth = '2021-03-21';
        $twoMonthsBehind = '2021-02-21';
        $tenDaysBehind = '2021-04-10';

        return [
            [[
                'first_payment_at' => $yesterday,
            ], [
                'firstPaymentAfter' => $twoDaysBehind,
            ]],
            [[
                'first_payment_at' => $yesterday,
            ], [
                'firstPaymentBefore' => $today,
            ]],
            [[
                'last_payment_at' => $yesterday,
            ], [
                'lastPaymentAfter' => $twoDaysBehind,
            ]],
            [[
                'last_payment_at' => $yesterday,
            ], [
                'lastPaymentBefore' => $today,
            ]],
            [[
                'first_payment_at' => $lastMonth,
                'last_payment_at' => $today,
            ], [
                'firstPaymentAfter' => $twoMonthsBehind,
                'firstPaymentBefore' => $tenDaysBehind,
            ]],
            [[
                'first_payment_at' => $lastMonth,
                'last_payment_at' => $yesterday,
            ], [
                'firstPaymentAfter' => $twoMonthsBehind,
                'firstPaymentBefore' => $yesterday,
                'lastPaymentAfter' => $tenDaysBehind,
                'lastPaymentBefore' => $today,
            ]],
            // Relative dates
            [[
                'first_payment_at' => Carbon::parse('-1 month')->toDateString(),
                'last_payment_at' => Carbon::parse('-2 days')->toDateString(),
            ], [
                'firstPaymentAfter' => '-2 months',
                'firstPaymentBefore' => 'yesterday',
                'lastPaymentAfter' => '-10 days',
                'lastPaymentBefore' => 'today',
            ]],
        ];
    }

    public function testDoesNotFilterOnNullOrEmptyStringPaymentDates(): void
    {
        Account::factory(5)->create();

        Account::factory()->state([
            'first_payment_at' => '-1month',
            'last_payment_at' => 'today',
        ])->create();

        $this
            ->actingAsUser($this->createUserWithPermissions('member.'))
            ->post(route('backend.member.listing'), [
                'firstPaymentAfter' => null,
                'firstPaymentBefore' => null,
                'lastPaymentAfter' => '',
                'lastPaymentBefore' => '',
            ])->assertOk()
            ->assertJsonCount(6, 'data');
    }

    /**
     * @dataProvider filterStatusAndCountDataProvider
     */
    public function testCanFilterOnRPPProfile(string $status, int $count): void
    {
        $oneAccount = $this->generateAccountWithPaymentMethods();
        $twoAccount = $this->generateAccountWithPaymentMethods();
        $threeAccount = $this->generateAccountWithPaymentMethods();

        $this->generateRPPProfile($oneAccount, RecurringPaymentProfileStatus::ACTIVE);
        $this->generateRPPProfile($oneAccount, RecurringPaymentProfileStatus::SUSPENDED);
        $this->generateRPPProfile($twoAccount, RecurringPaymentProfileStatus::ACTIVE);
        $this->generateRPPProfile($threeAccount, RecurringPaymentProfileStatus::SUSPENDED);
        $this->generateRPPProfile($threeAccount, RecurringPaymentProfileStatus::CANCELLED);

        $this
            ->actingAsUser($this->createUserWithPermissions('member.'))
            ->post(route('backend.member.listing'), [
                'rpp' => $status,
            ])->assertOk()
            ->assertJsonCount($count, 'data');
    }

    public function filterStatusAndCountDataProvider(): array
    {
        return [
            [RecurringPaymentProfileStatus::ACTIVE, 2],
            [RecurringPaymentProfileStatus::CANCELLED, 1],
            [RecurringPaymentProfileStatus::SUSPENDED, 2],
            ['fishy-value', 3],
        ];
    }

    protected function generateRPPProfile($account, $status): void
    {
        $rpps = $this->generateRpps($account, $account->defaultPaymentMethod, 1, 'CAD');
        collect($rpps)->each(function ($rpp) use ($status) {
            $rpp->status = $status;
            $rpp->save();
        });
    }

    /**
     * @dataProvider archiveStatusDataProvider
     */
    public function testCanFilterSupporterOnStatuses(?int $status, int $expectedCount): void
    {
        Member::factory(4)->create();
        Member::factory()->archived()->create();

        $this
            ->actingAsUser($this->createUserWithPermissions('member.'))
            ->post(route('backend.member.listing'), ['fA' => $status])
            ->assertOk()
            ->assertJsonCount($expectedCount, 'data');
    }

    public function archiveStatusDataProvider(): array
    {
        return [
            [SupporterStatus::ACTIVE, 4],
            [SupporterStatus::ARCHIVED, 1],
            [SupporterStatus::ALL, 5],
            [null, 4],
        ];
    }

    /**
     * @dataProvider paymentMethodsDataProvider
     */
    public function testCanFilterSupportersOnPaymentMethods(string $filter, int $expectedCount, ?string $name = null): void
    {
        Member::factory(3)->create([
            'first_name' => 'No',
            'last_name' => 'Payments',
        ]);

        Member::factory()->hasPaymentMethods()->create([
            'first_name' => 'Some',
            'last_name' => 'Payments',
        ]);

        Member::factory()->has(PaymentMethod::factory()->expired())->create([
            'first_name' => 'Expired',
            'last_name' => 'Payment',
        ]);

        Member::factory()->has(PaymentMethod::factory()->expiringByEndOfNextMonth())->create([
            'first_name' => 'Expiring',
            'last_name' => 'Payment',
        ]);

        $response =
            $this
                ->actingAsUser($this->createUserWithPermissions('member.'))
                ->post(route('backend.member.listing'), ['payment_method' => $filter])
                ->assertOk()
                ->assertJsonCount($expectedCount, 'data');

        if ($name) {
            $this->assertStringContainsString($name, $response->json('data.0.1'));
        }
    }

    public function paymentMethodsDataProvider(): array
    {
        return [
            ['valid', 2],
            ['expired', 1, 'Expired Payment'],
            ['expiring', 1, 'Expiring Payment'],
            ['none', 3, 'No Payments'],
        ];
    }

    /**
     * @dataProvider canFilterOnSlippingSupportersProvider
     */
    public function testCanFilterOnSlippingSupporters(?string $isSlipping, int $expectedCount): void
    {
        Member::factory(2)->create();
        Member::factory(1)->has(PaymentMethod::factory())->create();
        Member::factory(3)->has(PaymentMethod::factory()->expiringByEndOfNextMonth())->create();

        $account = $this->generateAccountWithPaymentMethods();
        $this->generateSuspendedRpps($account, $account->defaultPaymentMethod);

        $this->actingAsAdminUser()
            ->post(route('backend.member.listing'), ['is_slipping' => $isSlipping])
            ->assertOk()
            ->assertJsonCount($expectedCount, 'data');
    }

    public function canFilterOnSlippingSupportersProvider(): array
    {
        return [
            ['1', 4],
            ['0', 3],
            [null, 7],
        ];
    }

    /**
     * @dataProvider canFilterOnUsedTextToGiveSupportersProvider
     */
    public function testCanFilterOnUsedTextToGiveSupporters(?string $usedTextToGive, int $expectedCount): void
    {
        Member::factory(2)->create();
        Order::factory()->conversation()->paid()->create();

        $this->actingAsAdminUser()
            ->post(route('backend.member.listing'), ['used_text_to_give' => $usedTextToGive])
            ->assertOk()
            ->assertJsonCount($expectedCount, 'data');
    }

    public function canFilterOnUsedTextToGiveSupportersProvider(): array
    {
        return [
            ['1', 1],
            ['0', 3],
            [null, 3],
        ];
    }

    /**
     * @dataProvider canFilterOnSupportersWithALoginProvider
     */
    public function testCanFilterOnSupportersWithALogin(?string $hasLogin, int $expectedCount): void
    {
        Member::factory(3)->create();
        Member::factory(2)->create(['password' => null]);

        $this->actingAsAdminUser()
            ->post(route('backend.member.listing'), ['has_login' => $hasLogin])
            ->assertOk()
            ->assertJsonCount($expectedCount, 'data');
    }

    public function canFilterOnSupportersWithALoginProvider(): array
    {
        return [
            ['1', 3],
            ['0', 2],
            [null, 5],
        ];
    }

    /**
     * @param bool|string $hasLoggedIn
     *
     * @dataProvider donorActivityLoggedInStatusDataProvider
     */
    public function testCanFilterOnDonorActivity($hasLoggedIn, int $expectedCount): void
    {
        Member::factory(4)->create();
        Member::factory()->hasLoginAuditLogs(1)->create();
        /*
         * Members can be impersonated by an admin.
         * These should not be taken into account when looking if a member has logged in.
         */
        Member::factory()->has(MemberLogin::factory(3)->impersonated(), 'loginAuditLogs')->create();

        $this
            ->actingAsUser($this->createUserWithPermissions('member.'))
            ->post(route('backend.member.listing'), ['has_logged_in' => $hasLoggedIn])
            ->assertOk()
            ->assertJsonCount($expectedCount, 'data');
    }

    public function donorActivityLoggedInStatusDataProvider(): array
    {
        return [
            [true, 1],
            [false, 5],
            ['1', 1],
            ['0', 5],
        ];
    }

    /**
     * @dataProvider verifiedStatusDataProvilder
     */
    public function testCanFilterOnVerifiedStatus($filter, $expected = 1): void
    {
        Member::factory()->create();
        Member::factory()->verified()->create();
        Member::factory()->pending()->create();
        Member::factory()->denied()->create();

        $this
            ->actingAsUser($this->createUserWithPermissions('member.'))
            ->post(route('backend.member.listing'), ['verified_status' => $filter])
            ->assertOk()
            ->assertJsonCount($expected, 'data');
    }

    public function verifiedStatusDataProvilder(): array
    {
        return [
            ['1', 1], // Verified
            ['0', 1], // Pending
            ['-1', 1], // Denied
            ['2', 1], // Unverified
            [null, 4],
            ['fishy-value', 4],
        ];
    }

    public function testExportDoesOnlyIncludesMembershipWithEnrolledMembers(): void
    {
        $enrolledMembersMembershipName = 'Membership with enrolled members';
        $neverEnrolledMembershipName = 'No one ever enrolled here';

        Member::factory(3)
            ->hasAttached(Membership::factory()->create(['name' => $enrolledMembersMembershipName]), [], 'groups')
            ->create();

        Membership::factory()->create([
            'name' => $neverEnrolledMembershipName,
        ]);

        $response = $this
            ->actingAsUser($this->createUserWithPermissions('member.'))
            ->get(route('backend.member.export'))
            ->assertOk();

        $csvContent = $response->streamedContent();

        $this->assertStringContainsString($enrolledMembersMembershipName, $csvContent);
        $this->assertStringNotContainsString($neverEnrolledMembershipName, $csvContent);
    }

    /**
     * @dataProvider filterFundraiserPagesDataProvider
     */
    public function testCanFilterOnFundraiserPages(?string $status, int $count): void
    {
        Member::factory(3)->create();

        FundraisingPage::factory()->active()->create();
        FundraisingPage::factory()->closed()->create();
        FundraisingPage::factory()->deadlined()->create();

        $this
            ->actingAsUser($this->createUserWithPermissions('member.'))
            ->post(route('backend.member.listing'), ['fundraisers' => $status])
            ->assertOk()
            ->assertJsonCount($count, 'data');
    }

    public function filterFundraiserPagesDataProvider(): array
    {
        return [
            ['active', 1],
            ['closed', 2],
            ['never', 3],
            ['fishy-value', 6],
            [null, 6],
            ['', 6],
        ];
    }

    /**
     * @dataProvider canFilterEmailOptinProvider
     */
    public function testCanFilterEmailOptin(?string $optin, int $expectedCount): void
    {
        $optinMember = Member::factory()
            ->has(MemberOptinLog::factory()->optin(), 'optinLogs')
            ->create();
        $optoutMember = Member::factory(2)
            ->has(MemberOptinLog::factory()->optout(), 'optinLogs')
            ->create();

        $this->actingAsAdminUser()
            ->post(route('backend.member.listing'), ['fe' => $optin])
            ->assertOk()
            ->assertJsonCount($expectedCount, 'data');
    }

    public function canFilterEmailOptinProvider(): array
    {
        return [
            ['1', 1], // 1 optin
            ['0', 2], // 2 optout
            [null, 3], // 3 total
        ];
    }

    public function testCanFilterOnDonationForm(): void
    {
        Member::factory(3)->create();

        $member = Member::factory()->create();

        $product = Product::factory()->create(['type' => ProductType::DONATION_FORM]);

        Order::factory()
            ->for($member)
            ->has(
                OrderItem::factory()->for(
                    Variant::factory()->for($product)
                ),
                'items'
            )->create(['confirmationdatetime' => now()]);

        $this->actingAsAdminUser()
            ->post(route('backend.member.listing'), ['donationForms' => $product->hashid])
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    private function assertMemberExported(Member $member, string $csvFileContent): void
    {
        $this->assertStringContainsString($member->accountType->name ?? '', $csvFileContent);
        $this->assertStringContainsString($member->display_name, $csvFileContent);
        $this->assertStringContainsString($member->first_name, $csvFileContent);
        $this->assertStringContainsString($member->last_name, $csvFileContent);
        $this->assertStringContainsString($member->bill_organization_name, $csvFileContent);
        $this->assertStringContainsString($member->email, $csvFileContent);
        $this->assertStringContainsString($member->email_opt_in ? 'Yes' : 'No', $csvFileContent);
        $this->assertStringContainsString($member->ship_title, $csvFileContent);
        $this->assertStringContainsString($member->ship_first_name, $csvFileContent);
        $this->assertStringContainsString($member->ship_last_name, $csvFileContent);
        $this->assertStringContainsString($member->ship_organization_name, $csvFileContent);
        $this->assertStringContainsString($member->ship_email, $csvFileContent);
        $this->assertStringContainsString($member->ship_address_01, $csvFileContent);
        $this->assertStringContainsString($member->ship_address_02 ?: '', $csvFileContent);
        $this->assertStringContainsString($member->ship_city, $csvFileContent);
        $this->assertStringContainsString($member->ship_state, $csvFileContent);
        $this->assertStringContainsString($member->ship_zip, $csvFileContent);
        $this->assertStringContainsString($member->ship_country, $csvFileContent);
        $this->assertStringContainsString($member->ship_phone, $csvFileContent);
        $this->assertStringContainsString($member->bill_title, $csvFileContent);
        $this->assertStringContainsString($member->bill_first_name, $csvFileContent);
        $this->assertStringContainsString($member->bill_last_name, $csvFileContent);
        $this->assertStringContainsString($member->bill_organization_name, $csvFileContent);
        $this->assertStringContainsString($member->bill_email, $csvFileContent);
        $this->assertStringContainsString($member->bill_address_01, $csvFileContent);
        $this->assertStringContainsString($member->bill_address_02 ?: '', $csvFileContent);
        $this->assertStringContainsString($member->bill_city, $csvFileContent);
        $this->assertStringContainsString($member->bill_state, $csvFileContent);
        $this->assertStringContainsString($member->bill_zip, $csvFileContent);
        $this->assertStringContainsString($member->bill_country, $csvFileContent);
        $this->assertStringContainsString($member->bill_phone, $csvFileContent);
        $this->assertStringContainsString($member->donor_id ?: '', $csvFileContent);
        $this->assertStringContainsString($member->lifetime_purchase_count + $member->lifetime_donation_count, $csvFileContent);
        $this->assertStringContainsString(money($member->lifetime_purchase_amount + $member->lifetime_donation_amount)->format(), $csvFileContent);
        $this->assertStringContainsString($member->created_at, $csvFileContent);
        $this->assertStringContainsString($member->updated_at, $csvFileContent);
        $this->assertStringContainsString($member->referral_source ?: '', $csvFileContent);
    }
}
