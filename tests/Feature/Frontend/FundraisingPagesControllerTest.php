<?php

namespace Tests\Feature\Frontend;

use Ds\Models\FundraisingPage;
use Ds\Models\Member;
use Ds\Models\Observers\FundraisingPageObserver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FundraisingPagesControllerTest extends TestCase
{
    public function testPreventingEditingWithoutEditFundraiserFeature(): void
    {
        $fundraisingPage = tap($this->createFundraisingPageWithoutExchangeRate())->save();

        sys_set('account_login_features', ['edit-fundraisers']);

        $this->actingAsAccount($fundraisingPage->memberOrganizer)
            ->get($fundraisingPage->absolute_edit_url)
            ->assertOk();

        sys_set('account_login_features', []);

        $this->actingAsAccount($fundraisingPage->memberOrganizer)
            ->get($fundraisingPage->absolute_edit_url)
            ->assertNotFound();
    }

    public function testPreventingUpdatingWithoutEditFundraiserFeature(): void
    {
        $fundraisingPage = tap($this->createFundraisingPageWithoutExchangeRate())->save();

        sys_set('fundraising_pages_profanity_filter', false);
        sys_set('account_login_features', ['edit-fundraisers']);

        $this->actingAsAccount($fundraisingPage->memberOrganizer)
            ->post(
                route('frontend.fundraising_pages.update', [$fundraisingPage->url]),
                $this->buildInsertRequest($fundraisingPage)
            )->assertRedirect();

        sys_set('account_login_features', []);

        $this->actingAsAccount($fundraisingPage->memberOrganizer)
            ->post(
                route('frontend.fundraising_pages.update', [$fundraisingPage->url]),
                $this->buildInsertRequest($fundraisingPage)
            )->assertNotFound();
    }

    public function testInsertFundraisingPageSuccessful(): void
    {
        $newFundraisingPage = $this->createFundraisingPageWithoutExchangeRate();

        sys_set('fundraising_pages_profanity_filter', false);

        $this
            ->actingAsAccount(Member::factory()->individual()->create())
            ->post(route('frontend.fundraising_pages.insert'), $this->buildInsertRequest($newFundraisingPage))
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect($newFundraisingPage->absolute_url);
    }

    public function testInsertFundraisingPageNewMemberSuccessful(): void
    {
        $newFundraisingPage = $this->createFundraisingPageWithoutExchangeRate();

        $newMember = Member::factory()->individual()->make();

        sys_set('fundraising_pages_profanity_filter', false);

        $this
            ->post(route('frontend.fundraising_pages.insert'), $this->buildInsertRequest($newFundraisingPage, [
                'postal_code' => $newMember->bill_zip,
                'first_name' => $newMember->first_name,
                'last_name' => $newMember->last_name,
                'email' => $newMember->email,
                'password' => 'password',
            ]))->assertSessionDoesntHaveErrors()
            ->assertRedirect($newFundraisingPage->absolute_url);
    }

    public function testInsertFundraisingPageFailsWhenWrongGoalDeadline(): void
    {
        $newFundraisingPage = $this->createFundraisingPageWithoutExchangeRate();

        sys_set('fundraising_pages_profanity_filter', false);

        $this
            ->actingAsAccount(Member::factory()->individual()->create())
            ->from(route('frontend.fundraising_pages.create'))
            ->post(
                route('frontend.fundraising_pages.insert'),
                $this->buildInsertRequest($newFundraisingPage, ['goal_deadline' => 'invalid date'])
            )
            ->assertRedirect(route('frontend.fundraising_pages.create'))
            ->assertSessionHasErrors('goal_deadline');
    }

    private function createFundraisingPageWithoutExchangeRate(): FundraisingPage
    {
        // Set same currency for system and fundraising page
        // to avoid calling Swap exchange rate facade.
        $currencyCode = 'CAD';
        sys_set('dpo_currency', $currencyCode);

        $newFundraisingPage = FundraisingPage::factory()->make(['currency_code' => $currencyCode]);

        // Call observer creating() to generate the appropriate url.
        (new FundraisingPageObserver())->creating($newFundraisingPage);

        return $newFundraisingPage;
    }

    private function buildInsertRequest(FundraisingPage $fundraisingPage, array $overrides = []): array
    {
        Storage::fake(config('filesystems.cloud'));

        return array_merge([
            'page_name' => $fundraisingPage->title,
            'page_type_id' => $fundraisingPage->product_id,
            'category' => $fundraisingPage->category,
            'content' => 'testing content of a fundraising page.',
            'currency_code' => $fundraisingPage->currency_code,
            'goal_deadline' => $fundraisingPage->goal_deadline,
            'goal_amount' => $fundraisingPage->goal_amount,
            'page_photo' => UploadedFile::fake()->create('file.jpg'),
            'is_team' => $fundraisingPage->is_team,
            'video' => $fundraisingPage->video_url,
            'page_photo' => '0',
        ], $overrides);
    }
}
