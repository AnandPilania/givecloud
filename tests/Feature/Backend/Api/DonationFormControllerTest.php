<?php

namespace Tests\Feature\Backend\Api;

use Ds\Models\Product;
use Tests\TestCase;

class DonationFormControllerTest extends TestCase
{
    public function testAbortsWhenFeatureIsDisabled(): void
    {
        $this->actingAsUser();
        sys_set('feature_fundraising_forms', 0);

        $this->get(route('api.donation-forms.index'))
            ->assertSessionHas('_flashMessages.error', 'The <strong>fundraising_forms</strong> feature is not enabled.');
    }

    public function testIndexReturnsDonationFormResourceCollection(): void
    {
        $this->actingAsAdminUser();

        $product = Product::factory()->donationForm()->create();

        $this->get(route('api.donation-forms.index'))
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $product->hashid);
    }

    public function testShowReturnsDonationFormResource(): void
    {
        $this->actingAsAdminUser();

        $product = Product::factory()->donationForm()->create();

        $this->get(route('api.donation-forms.show', $product->hashid))
            ->assertJsonPath('data.id', $product->hashid);
    }

    public function testStoreSavesFormAndReturnsForm(): void
    {
        $this->actingAsAdminUser();

        $name = 'My new test form';

        $this->post(route('api.donation-forms.store'), [
            'default_amount_type' => 'automatic',
            'name' => $name,
        ])->assertJsonPath('data.name', $name);
    }

    public function testUpdateUpdatesFormAndReturnsForm(): void
    {
        $this->actingAsAdminUser();

        $product = Product::factory()->donationForm()->create();
        $name = 'My updated name';

        $this->patch(route('api.donation-forms.update', $product->hashid), [
            'default_amount_type' => 'automatic',
            'name' => $name,
        ])->assertJsonPath('data.name', $name);
    }

    public function testDestroyReturnsErrorWhenDefaultForm(): void
    {
        $this->actingAsAdminUser();

        $product = Product::factory()
            ->donationForm()
            ->create([
                'metadata' => [
                    'donation_forms_is_default_form' => true,
                ],
            ]);

        $this->delete(route('api.donation-forms.destroy', $product->hashid))
            ->assertUnprocessable()
            ->assertJsonPath('error', 'You cannot delete your default form, you must identity another form as default first.');
    }

    public function testDestroyReturnsErrorWhenSoleForm(): void
    {
        $this->actingAsAdminUser();

        $product = Product::donationForms()->first();
        $product->metadata(['donation_forms_is_default_form' => false]);
        $product->save();

        $this->delete(route('api.donation-forms.destroy', $product->hashid))
            ->assertUnprocessable()
            ->assertJsonPath('error', 'You must have at least one form.');
    }

    public function testDestroyDestroysForm(): void
    {
        $this->actingAsAdminUser();

        $products = Product::factory(4)
            ->donationForm()
            ->create([
                'metadata' => [
                    'donation_forms_is_default_form' => false,
                ],
            ]);

        $this->delete(route('api.donation-forms.destroy', $products->first()->hashid))
            ->assertOk();

        $this->assertSoftDeleted('product', [
            'id' => Product::decodeHashid($products->first()->hashid),
        ]);
    }

    public function testMarkAsDefaultMarksFormAsDefault(): void
    {
        $this->actingAsAdminUser();

        $default = Product::factory()
            ->donationForm()
            ->create([
                'metadata' => [
                    'donation_forms_is_default_form' => true,
                ],
            ]);

        $newDefault = Product::factory()->donationForm()->create();

        $this->post(route('api.donation-forms.make-default', $newDefault->hashid))
            ->assertJsonPath('data.is_default_form', true);

        $this->get(route('api.donation-forms.show', $default->hashid))
            ->assertJsonPath('data.is_default_form', false);
    }

    public function testReplicateCopiesFormAndUnSetsDefaultForm(): void
    {
        $this->actingAsAdminUser();

        $product = Product::factory()
            ->donationForm()
            ->create([
                'metadata' => [
                    'donation_forms_is_default_form' => true,
                ],
            ]);

        $this->post(route('api.donation-forms.replicate', $product->hashid))
            ->assertCreated()
            ->assertJsonPath('data.name', "(COPY) $product->name")
            ->assertJsonPath('data.is_default_form', false);
    }

    public function testRestoreRestoresDeletedForm(): void
    {
        $this->actingAsAdminUser();

        $product = Product::factory()->donationForm()->create();

        $product->delete();

        $this->assertSoftDeleted('product', [
            'id' => Product::decodeHashid($product->hashid),
        ]);

        $this->post(route('api.donation-forms.restore', $product->hashid))
            ->assertOk();

        $this->assertNotSoftDeleted('product', [
            'id' => Product::decodeHashid($product->hashid),
        ]);
    }
}
