<?php

namespace Tests\Browser\Frontend\Embeddable;

use Ds\Models\Product;
use Ds\Models\Variant;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Frontend\Embeddable\DonationFormPage;
use Tests\DuskTestCase;

/**
 * @group orders
 */
class DonationFormTest extends DuskTestCase
{
    public function testFormValidation()
    {
        $this->browse(function (Browser $browser) {
            $product = Product::factory()->create();
            $oneTimeVariant = Variant::factory()->donation()->create(['variantname' => 'One Time', 'isdefault' => true, 'sequence' => 1]);
            $monthlyVariant = Variant::factory()->donation()->create(['variantname' => 'Monthly', 'sequence' => 2]);
            $product->variants()->saveMany([$oneTimeVariant, $monthlyVariant]);
            $minimumAmountError = 'Please choose a valid donation amount. (Minimum: $10.00)';

            $browser
                ->visit(new DonationFormPage($product))
                ->click('@next_step')
                ->assertSeeIn('@form_error', $minimumAmountError)
                ->type('@other_amount', '2')
                ->click('@next_step')
                ->assertSeeIn('@form_error', $minimumAmountError)
                ->type('@other_amount', '10')
                ->click('@next_step')
                ->assertMissing('@form_error')
                ->click('@previous_step')
                ->type('@other_amount', '2')
                ->click('@next_step')
                ->assertSeeIn('@form_error', $minimumAmountError)
                ->click('input[aria-label="Donation amount 25"]')
                ->click('@next_step')
                ->assertMissing('@form_error');
        });
    }

    public function testSwitchingVariants()
    {
        $this->browse(function (Browser $browser) {
            $product = Product::factory()->create();
            $oneTimeVariant = Variant::factory()->donation()->create(['variantname' => 'One Time', 'isdefault' => true, 'sequence' => 1]);
            $monthlyVariant = Variant::factory()->donation()->create(['variantname' => 'Monthly', 'sequence' => 2]);
            $product->variants()->saveMany([$oneTimeVariant, $monthlyVariant]);
            $minimumAmountError = 'Please choose a valid donation amount. (Minimum: $10.00)';

            $browser
                ->visit(new DonationFormPage($product))
                ->click('input[aria-label="Donation amount 25"]')
                ->click("label[aria-label=\"{$monthlyVariant->variantname}\"]")
                ->type('@other_amount', '21')
                ->assertDataAttribute('@donation_value', 'donation-value', '21');
        });
    }
}
