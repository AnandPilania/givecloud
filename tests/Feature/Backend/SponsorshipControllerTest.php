<?php

namespace Tests\Feature\Backend;

use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Models\Member;
use Tests\TestCase;

class SponsorshipControllerTest extends TestCase
{
    public function testSaveSuccessful(): void
    {
        $sponsorship = Sponsorship::factory()->create();

        $this
            ->actingAsUser()
            ->post(route('backend.sponsorship.save'), ['id' => $sponsorship->getKey()])
            ->assertRedirect()
            ->assertLocation(route('backend.sponsorship.view', ['id' => $sponsorship]));
    }

    public function testSaveWrongBirthDate(): void
    {
        $sponsorship = Sponsorship::factory()->create();
        $previousUrl = route('backend.sponsorship.index');

        // Set previous url to return to on failing.
        $this->actingAsUser()->get($previousUrl);

        $this
            ->post(route('backend.sponsorship.save'), [
                'id' => $sponsorship->getKey(),
                'birth_date' => 'Jul 11, 20112',
            ])->assertRedirect()
            ->assertLocation($previousUrl)
            ->assertSessionHas('_flashMessages.error', 'The birth date is not a valid date.');
    }

    /**
     * @dataProvider sponsorShipMissingAttributesDataProvider
     */
    public function testSearchReturnsResultsWhenAttributesAreNull($firstName, $lastName): void
    {
        Sponsorship::factory([
            'first_name' => $firstName,
            'last_name' => $lastName,
        ])->create();

        // will search for a substr of concatenated $firstName and $lastName of 3 characters minimum.
        $fullName = $firstName . ' ' . $lastName;
        $start = mt_rand(0, strlen($fullName) - 3);
        $length = mt_rand(3, strlen($fullName));
        $term = substr($fullName, $start, $length);

        $this->actingAsAdminUser()
            ->postJson(route('backend.sponsorship.ajax'), [
                'search' => $term,
            ])->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.2', ($lastName ?? '[none]') . ', ' . ($firstName ?? '[none]'));
    }

    public function sponsorShipMissingAttributesDataProvider(): array
    {
        return [
            ['Philippe', null],
            [null, 'Perusse'],
            ['Philippe', 'Perusse'],
        ];
    }

    public function testViewSuccessful(): void
    {
        $sponsorship = Sponsorship::factory()
            ->has(Sponsor::factory(3)->for(Member::factory()))
            ->create(['sponsor_count' => 3]);

        $this->actingAsUser($this->createUserWithPermissions(['sponsorship.view', 'sponsor.view']));
        $response = $this->get(route('backend.sponsorship.view', $sponsorship));

        $response->assertOk();
        $response->assertSee($sponsorship->reference_number);
        $response->assertSee($sponsorship->sponsors->first()->member->display_name);
    }

    public function testViewWhenMissingMemberSuccessful(): void
    {
        $sponsorship = Sponsorship::factory()
            ->has(Sponsor::factory(3))
            ->create(['sponsor_count' => 3]);

        $this->actingAsUser($this->createUserWithPermissions(['sponsorship.view', 'sponsor.view']));
        $response = $this->get(route('backend.sponsorship.view', $sponsorship));

        $response->assertOk();
        $response->assertSee($sponsorship->reference_number);
    }
}
