<?php

namespace Tests\Browser\Frontend\Orders;

use Ds\Models\FundraisingPage;
use Ds\Models\Member;
use Ds\Models\Product;
use Ds\Models\Variant;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Frontend\Carts\BasketPage;
use Tests\Browser\Pages\Frontend\Carts\BillingPage;
use Tests\Browser\Pages\Frontend\Carts\PaymentPage;
use Tests\Browser\Pages\Frontend\Carts\ShippingPage;
use Tests\Browser\Pages\Frontend\Carts\ThankYouPage;
use Tests\Browser\Pages\Frontend\FundraisingPages\FundraisingCreatePage;
use Tests\Browser\Pages\Frontend\FundraisingPages\FundraisingPagesPage;
use Tests\Browser\Pages\Frontend\FundraisingPages\FundraisingViewPage;
use Tests\Browser\Pages\Frontend\Products\ProductPage;
use Tests\DuskTestCase;

/**
 * @group orders
 */
class OrdersCreateTest extends DuskTestCase
{
    public function testOrderAProduct()
    {
        $this->browse(function (Browser $browser) {
            $account = Member::factory()->individual()->nps()->create();
            $product = Product::factory()->allowOutOfStock()->create();
            $variant = Variant::factory()->create();
            $product->defaultVariant()->save($variant);
            $quantity = 3;

            // Cart items (variants) to select
            $cartItems = new Collection([(object) compact('variant', 'quantity')]);

            $browser
                ->loginAs($account, 'account_web')
                ->visit(new ProductPage($product))
                ->addToCart($quantity)
                ->click('@next_step')
                ->on(new BasketPage($cartItems))
                ->click('@next_step')
                ->on(new BillingPage())
                ->click('@next_step')
                ->on(new ShippingPage())
                ->click('@next_step')
                ->on(new PaymentPage($cartItems))
                ->selectReferral()
                ->payWithCreditCard()
                ->on(new ThankYouPage($account->lastOrder()->first()));
        });
    }

    public function testOrderFromFundraiser()
    {
        $this->browse(function (Browser $browser) {
            sys_set(['donor_title_options' => null]);

            $account = Member::factory()->individual()->nps()->create();
            $product = Product::factory()->create(['allow_fundraising_pages' => true]);
            $variants = Variant::factory(3)->create();
            $product->variants()->saveMany($variants);
            $product->defaultVariant()->save($variants->first());
            $fundraisingPageName = 'Testing Fundraising Page';
            $fundraisingPageLink = FundraisingPage::createUniqueUrl($fundraisingPageName);

            $browser
                ->loginAs($account, 'account_web')
                ->visit(new FundraisingPagesPage())
                ->clickCreate()
                ->on(new FundraisingCreatePage($product))
                ->typeNameAndSubmit($fundraisingPageName)
                ->on(new FundraisingViewPage($fundraisingPageLink, $variants))
                ->waitLoadingElements()
                ->selectDonation($variants->first()->getKey())
                ->fillInCreditCard()
                ->fillInBilling()
                ->click('@donate_button')
                ->waitForReload() // actually waiting for the current page to change
                ->on(new ThankYouPage($account->lastOrder()->first()));
        });
    }
}
