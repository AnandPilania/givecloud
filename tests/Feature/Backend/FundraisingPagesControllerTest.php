<?php

namespace Tests\Feature\Backend;

use Ds\Models\FundraisingPage;
use Ds\Models\Member;
use Tests\TestCase;

/**
 * @group backend
 * @group fundraisingpages
 */
class FundraisingPagesControllerTest extends TestCase
{
    public function testIndexJsonWithOnlyCreatedEndFilter(): void
    {
        $fundraisingPage = FundraisingPage::factory()->active()->create();

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsUser($this->createUserWithPermissions('fundraisingpages.'))
            ->post(route('backend.fundraising-pages.index_json'), [
                'created_end' => $fundraisingPage->created_at->addDay()->toDateString(),
            ]);

        $response
            ->assertOk()
            ->assertSeeText($fundraisingPage->name);

        $jsonResponse = $response->decodeResponseJson();
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);

        $this->assertCount(1, $jsonResponse['data']);
    }

    public function testIndexJsonWithOnlyCreatedStartFilter(): void
    {
        $fundraisingPage = FundraisingPage::factory()->active()->create();

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsUser($this->createUserWithPermissions('fundraisingpages.'))
            ->post(route('backend.fundraising-pages.index_json'), [
                'created_start' => $fundraisingPage->created_at->subDay()->toDateString(),
            ]);

        $response
            ->assertOk()
            ->assertSeeText($fundraisingPage->name);

        $jsonResponse = $response->decodeResponseJson();
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertCount(1, $jsonResponse['data']);
    }

    /**
     * @dataProvider statusDataProvider
     */
    public function testCanFilterOnStatus($filter, $expected = 1): void
    {
        FundraisingPage::factory(6)->create();
        FundraisingPage::factory(5)->active()->create();
        FundraisingPage::factory(4)->draft()->create();
        FundraisingPage::factory(3)->closed()->create();
        FundraisingPage::factory(2)->suspended()->create();
        FundraisingPage::factory(1)->reported()->create();

        FundraisingPage::factory()->active()->for(Member::factory()->pending(), 'memberOrganizer')->create();
        FundraisingPage::factory()->active()->for(Member::factory()->denied(), 'memberOrganizer')->create();

        $this
            ->actingAsUser($this->createUserWithPermissions('fundraisingpages.'))
            ->post(route('backend.fundraising-pages.index_json'), ['status' => $filter])
            ->assertOk()
            ->assertJsonCount($expected, 'data');
    }

    public function statusDataProvider(): array
    {
        return [
            [null, 8], // Default : active (active + reported)
            ['active-abuse', 1],
            ['any', 23],
            ['closed', 3],
            ['draft', 10],
            ['suspended', 2],
            ['pending', 22], // All non-specified Member (unverified) + pending
            ['denied', 1],
        ];
    }

    public function testCanFilterOnFundraiser(): void
    {
        FundraisingPage::factory(5)->active()->create();
        $member = Member::factory()->create();
        FundraisingPage::factory()->active()->for($member, 'memberOrganizer')->create();

        $this
            ->actingAsUser($this->createUserWithPermissions('fundraisingpages.'))
            ->post(route('backend.fundraising-pages.index_json'), ['fundraiser' => $member->getKey()])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertSeeText($member->display_name);
    }
}
