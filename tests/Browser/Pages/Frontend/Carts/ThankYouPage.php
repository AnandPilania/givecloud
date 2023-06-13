<?php

namespace Tests\Browser\Pages\Frontend\Carts;

use Ds\Models\Order;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class ThankYouPage extends Page
{
    /** @var \Ds\Models\Order */
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('frontend.orders.thank_you', [$this->order->client_uuid], false);
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser
            ->assertPathIs($this->url())
            ->assertSee('View Your Receipt');
    }
}
