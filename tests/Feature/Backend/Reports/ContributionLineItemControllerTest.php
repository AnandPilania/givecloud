<?php

namespace Tests\Feature\Backend\Reports;

use Ds\Domain\Sponsorship\Models\Segment;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Enums\LedgerEntryType;
use Ds\Enums\PaymentType;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Account;
use Ds\Models\AccountType;
use Ds\Models\LedgerEntry;
use Ds\Models\Member;
use Ds\Models\Membership;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Payment;
use Ds\Models\Product;
use Ds\Models\ProductCategory;
use Ds\Models\ShippingMethod;
use Ds\Models\Tax;
use Ds\Models\User;
use Ds\Models\Variant;
use Ds\Services\LedgerEntryService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

class ContributionLineItemControllerTest extends TestCase
{
    use InteractsWithRpps;
    use WithFaker;

    public function testCanReturnEntries(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders(4));
        $this->createLedgerEntriesForModels($this->createTransactionsWithRPP());

        $this->assertFilteredRequestCount([], LedgerEntry::count());
    }

    public function testCanFilterOnAccountType(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders(4));

        $accountType = AccountType::factory()->create();
        $member = Member::factory()->for($accountType)->create();

        $order = $this->createOrders(1, ['order' => ['member_id' => $member]]);
        $this->createLedgerEntriesForModels($order);

        $this->actingAs(User::factory()->create())
            ->post(route('backend.reports.contribution-line-items.listing'), ['account_type' => $accountType->id])
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($member) {
                $json->has('data', 4, function (AssertableJson $json) use ($member) {
                    $json->where(1, function ($d) use ($member) {
                        return Str::contains($d, $member->display_name);
                    })->etc();
                })->etc();
            });
    }

    public function testCanFilterOnBillingCountry(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders(4, ['order' => ['billingcountry' => 'US']]));
        $this->createLedgerEntriesForModels($this->createOrders(1, ['order' => ['billingcountry' => 'CA']]));

        $this->assertFilteredRequestCount(['billing_country' => 'CA']);
    }

    /** @dataProvider capturedDatesDataProvider */
    public function testFilterOnCapturedAtDates(array $filter, int $expected): void
    {
        $this->createLedgerEntriesForModels($this->createOrders(4, ['order' => ['ordered_at' => '-1month']]));
        $this->createLedgerEntriesForModels($this->createOrders(1, ['order' => ['ordered_at' => toUtc('yesterday')]]));

        $this->assertFilteredRequestCount($filter, $expected);
    }

    public function capturedDatesDataProvider(): array
    {
        return [
            [[
                'captured_before' => '+3day',
                'captured_after' => '-3day',
            ], 4],
            [[
                'captured_before' => '-2days',
                'captured_after' => 'today',
            ], 0],
            [['captured_before' => '+1day'], 20],
            [['captured_after' => '-10years'], 20],
        ];
    }

    public function testCanFilterOnCategories(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders(4));

        $order = $this->createOrder();
        $product = $order->items()->first()->variant->product;
        $category = ProductCategory::factory()->create();
        $product->categories()->save($category);

        $this->createLedgerEntriesForModels(collect([$order]));

        $this->assertFilteredRequestCount(['categories' => $category->id], 2);
    }

    public function testCanFilterOnIpCountry(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders(4, ['order' => ['ip_country' => 'US']]));
        $this->createLedgerEntriesForModels($this->createOrders(1, ['order' => ['ip_country' => 'CA']]));

        $this->assertFilteredRequestCount(['ip_country' => 'CA']);
    }

    public function testCanFilterOnItems(): void
    {
        $variant = Variant::factory()->forProduct()->create();
        $this->createLedgerEntriesForModels($this->createOrders(4));
        $this->createLedgerEntriesForModels($this->createOrders(1, ['item' => ['productinventoryid' => $variant->id]]));

        $this->assertFilteredRequestCount(['items' => $variant->id], 2);
    }

    public function testCanFilterOnAnyItems()
    {
        $variant = Variant::factory()->forProduct()->create();
        $this->createLedgerEntriesForModels($this->createOrders(4));
        $this->createLedgerEntriesForModels($this->createOrders(1, ['item' => ['productinventoryid' => $variant->id]]));

        $this->assertFilteredRequestCount(['items' => '*'], 10);
    }

    public function testCanFilterOnFundraisingForms(): void
    {
        $variant = Variant::factory()->for(Product::factory()->donationForm())->create();

        $this->createLedgerEntriesForModels($this->createOrders(4));
        $this->createLedgerEntriesForModels($this->createOrders(1, ['item' => ['productinventoryid' => $variant->id]]));

        $this->assertFilteredRequestCount(['fundraising_forms' => $variant->product->hashid], 2);
    }

    public function testCanFilterOnGiftAid(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders(4, ['item' => ['gift_aid' => 0]]));
        $this->createLedgerEntriesForModels($this->createOrders(1, ['item' => ['gift_aid' => 1]]));

        $this->assertFilteredRequestCount(['gift_aid' => 1], 2);
    }

    /**
     * @dataProvider lineItemTypeDataProvider
     */
    public function testCanFilterOnLineItemType(string $type): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createOrders(1, ['item' => ['sponsorship_id' => Sponsorship::factory()]]));
        $this->createLedgerEntriesForModels($this->createOrders(1, [
            'order' => [
                'shipping_amount' => $this->faker->randomFloat(2, 1),
                'shipping_method_id' => ShippingMethod::factory(),
            ], ]));

        $this->assertFilteredRequestCount(['line_item_type' => $type], 3);
    }

    public function lineItemTypeDataProvider(): array
    {
        return [
            [LedgerEntryType::SHIPPING],
            [LedgerEntryType::LINE_ITEM],
            [LedgerEntryType::TAX],
            [LedgerEntryType::DCC],
        ];
    }

    public function testCanFilterOnMembership(): void
    {
        $membership = Membership::factory()->create();
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createOrders(1, ['variant' => ['membership_id' => $membership]]));

        $this->assertFilteredRequestCount(['membership' => $membership->id], 2);
    }

    public function testCanFilterOnAnyMembership(): void
    {
        $membership = Membership::factory()->create();
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createOrders(1, ['variant' => ['membership_id' => $membership]]));

        $this->assertFilteredRequestCount(['membership' => '*'], 2);
    }

    public function testCanFilterOnPaymentGateway(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());
        $order = $this->createOrder();

        $order->payments()->save(Payment::factory()->create(['gateway_type' => 'test_gateway']));
        $order->save();

        $this->createLedgerEntriesForModels(collect([$order]));

        $this->assertFilteredRequestCount(['gateway' => 'test_gateway']);
    }

    public function testCanFilterOnPaymentMethod(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());
        $order = $this->createOrder();

        $order->payments()->save(Payment::factory()->create(['type' => PaymentType::CHEQUE]));
        $order->save();

        $this->createLedgerEntriesForModels(collect([$order]));

        $this->assertFilteredRequestCount(['payment_method' => PaymentType::CHEQUE]);
    }

    public function testCanFilterOnRecurringPayments(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createTransactionsWithRPP());

        $this->assertFilteredRequestCount(['recurring' => 'onetime'], 5);
        $this->assertFilteredRequestCount(['recurring' => 'recurring']);
    }

    public function testCanFilterOnSponsorshipCustomTextFields(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createTransactionsWithRPP());

        $textSegment = Segment::factory()->create();
        $sponsorship = Sponsorship::factory()->create();
        $sponsorship->segments()->save($textSegment, ['value' => 'test']);
        $this->createLedgerEntriesForModels($this->createOrders(1, ['item' => ['sponsorship_id' => $sponsorship]]));

        $this->assertFilteredRequestCount(['segment' => [$textSegment->id => 'test']], 2);
    }

    public function testCanFilterOnSponsorshipCustomMultiSelectFields(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createTransactionsWithRPP());

        $selectSegment = Segment::factory()->hasItems(3)->multiSelect()->create();
        $item = $selectSegment->items->random();

        $sponsorship = Sponsorship::factory()->create();
        $sponsorship->segments()->save($selectSegment, ['segment_item_id' => $item->id]);

        $this->createLedgerEntriesForModels($this->createOrders(1, ['item' => ['sponsorship_id' => $sponsorship]]));

        $this->assertFilteredRequestCount(['segment' => [$selectSegment->id => $item->id]], 2);
    }

    public function testCanFilterOnSponsorships(): void
    {
        $sponsorship = Sponsorship::factory()->create();
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createOrders(1, ['item' => ['sponsorship_id' => $sponsorship]]));

        $this->assertFilteredRequestCount(['sponsorship' => $sponsorship->id], 2);
    }

    public function testCanFilterOnAnySponsorships(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createOrders(1, ['item' => ['sponsorship_id' => Sponsorship::factory()->create()]]));

        $this->assertFilteredRequestCount(['sponsorship' => '*'], 2);
    }

    public function testCanSearchOnInvoiceNumber(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());

        $order = $this->createOrder();
        $this->createLedgerEntriesForModels(collect([$order]));

        $this->assertFilteredRequestCount(['search' => $order->invoicenumber]);
    }

    public function testCanSearchOnMembershipName(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createTransactionsWithRPP());

        $membership = Membership::factory()->create(['name' => 'test_can_search_on_membership_name']);
        $this->createLedgerEntriesForModels($this->createOrders(1, ['variant' => ['membership_id' => $membership]]));

        $this->assertFilteredRequestCount(['search' => $membership->name], 2);
    }

    public function testCanSearchOnProductAndVariantName(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createTransactionsWithRPP());

        $order = $this->createOrder();
        $variant = $order->items()->first()->variant;

        $this->createLedgerEntriesForModels(collect([$order]));

        $this->assertFilteredRequestCount(['search' => $variant->variantname], 2);
        $this->assertFilteredRequestCount(['search' => $variant->product->name], 2);
    }

    public function testCanSearchOnRPPProfileId(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createTransactionsWithRPP());

        $transaction = $this->createTransactionWithRPP();
        $this->createLedgerEntriesForModels(collect([$transaction]));

        $this->assertFilteredRequestCount(['search' => $transaction->recurringPaymentProfile->profile_id]);
    }

    public function testCanSearchOnSupporterName(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createTransactionsWithRPP());

        $account = Account::factory()->create(['last_name' => 'test_can_search_on_supporter_name']);
        $order = $this->createOrder(['order' => ['member_id' => $account]]);
        $this->createLedgerEntriesForModels(collect([$order]));

        $this->assertFilteredRequestCount(['search' => $account->last_name]);
    }

    public function testCanSearchOnSponsorshipName(): void
    {
        $this->createLedgerEntriesForModels($this->createOrders());
        $this->createLedgerEntriesForModels($this->createTransactionsWithRPP());

        $sponsorship = Sponsorship::factory()->create([
            'first_name' => 'test_can_search_on_sponsorship_first_name',
            'last_name' => 'test_can_search_on_sponsorship_last_name',
        ]);

        $this->createLedgerEntriesForModels($this->createOrders(1, ['item' => ['sponsorship_id' => $sponsorship]]));

        $this->assertFilteredRequestCount(['search' => $sponsorship->first_name], 2);
        $this->assertFilteredRequestCount(['search' => $sponsorship->last_name], 2);
        $this->assertFilteredRequestCount(['search' => $sponsorship->first_name . ' ' . $sponsorship->last_name], 2);
    }

    public function testCanExport(): void
    {
        $order = $this->createOrder();
        $transaction = $this->createTransactionWithRPP();

        $this->createLedgerEntriesForModels(collect([$order]));
        $this->createLedgerEntriesForModels(collect([$transaction]));

        $response = $this
            ->actingAsUser(User::factory()->create())
            ->get(route('backend.reports.contribution-line-items.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename=contribution-line-items.csv');

        $csvFileContent = $response->streamedContent();

        $this->assertStringContainsString('Contribution #' . $order->invoicenumber, $csvFileContent);
        $this->assertStringContainsString('Recurring Payment #' . $transaction->recurringPaymentProfile->profile_id, $csvFileContent);
    }

    private function assertFilteredRequestCount(array $filter, int $expected = 4): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('backend.reports.contribution-line-items.listing'), $filter)
            ->assertOk()
            ->assertJsonCount($expected, 'data');
    }

    private function createLedgerEntriesForModels(Collection $model): void
    {
        $model->each(function (Model $model) {
            $this->app->make(LedgerEntryService::class)->make($model);
        });
    }

    private function createOrders(int $count = 1, array $states = []): Collection
    {
        $product = Variant::factory()->for(
            Product::factory()->state($states['product'] ?? [])
        )->state($states['variant'] ?? []);

        return
            Order::factory($count)
                ->paid()
                ->shipped()
                ->taxed()
                ->state($states['order'] ?? [])
                ->has(
                    OrderItem::factory()
                        ->for($product)
                        ->state($states['item'] ?? [])
                        ->recurring_dcc()
                        ->hasAttached(Tax::factory(), ['amount' => $this->faker->randomFloat(2, 0.01)]),
                    'items'
                )
                ->create()
                ->load('items');
    }

    private function createOrder(array $states = []): Order
    {
        return $this->createOrders(1, $states)->first();
    }
}
