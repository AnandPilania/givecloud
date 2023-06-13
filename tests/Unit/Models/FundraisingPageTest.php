<?php

namespace Tests\Unit\Models;

use Ds\Models\FundraisingPage;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCase;

class FundraisingPageTest extends TestCase
{
    public function testUpdateAggregatesDoesNotIncludeDccAmountForOrderItems()
    {
        $items = OrderItem::factory(3)
            ->for(Order::factory()->paid())
            ->state(new Sequence(
                ['price' => 25, 'qty' => 1],
                ['price' => 10, 'qty' => 2],
                ['price' => 10, 'qty' => 1, 'dcc_amount' => 3],
            ))->create();

        $fundraisingPage = FundraisingPage::factory()->create();
        $fundraisingPage->paidOrderItems()->saveMany($items);
        $fundraisingPage->updateAggregates();

        $this->assertEquals(55, $fundraisingPage->amount_raised);
    }

    public function testAddTargetBlankToOutgoingLinksInDescription()
    {
        $page = FundraisingPage::factory()->create([
            'description' => '<a href="https://google.com">Google</a>',
        ]);

        $this->assertEquals(
            $page->description,
            '<a href="https://google.com" target="_blank" rel="noreferrer noopener">Google</a>'
        );
    }

    public function testPageIsViewableByAuthor(): void
    {
        $member = Member::factory()->create();

        /** @var \Ds\Models\FundraisingPage $page */
        $page = FundraisingPage::factory()->for($member, 'memberOrganizer')->create();

        $this->actingAsAccount($member);

        $this->assertTrue($page->isViewable());
    }

    /**
     * @dataProvider userPermissionDataProvider
     */
    public function testPageIsViewableByUserWithPermission(string $permission, bool $expected): void
    {
        /** @var \Ds\Models\FundraisingPage $page */
        $page = FundraisingPage::factory()->create();

        $this->actingAs($this->createUserWithPermissions($permission));

        $this->assertSame($expected, $page->isViewable());
    }

    public function userPermissionDataProvider(): array
    {
        return [
            ['fundraisingpages.edit', true],
            ['node.view', false],
            ['member.', false],
            ['', false],
        ];
    }

    /**
     * @dataProvider pageStatusDataProvider
     */
    public function testPageIsViewableByStatus(string $status, bool $expected): void
    {
        /** @var \Ds\Models\FundraisingPage $page */
        $page = FundraisingPage::factory()->create([
            'status' => $status,
        ]);

        $this->assertSame($expected, $page->isViewable());
    }

    public function pageStatusDataProvider(): array
    {
        return [
            ['active', true],
            ['pending', false],
            ['closed', false],
            ['suspended', false],
        ];
    }
}
