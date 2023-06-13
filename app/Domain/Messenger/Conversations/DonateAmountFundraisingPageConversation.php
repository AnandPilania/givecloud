<?php

namespace Ds\Domain\Messenger\Conversations;

use Ds\Common\DataAccess;
use Ds\Models\FundraisingPage;
use Ds\Models\Order;
use Ds\Models\Product;

class DonateAmountFundraisingPageConversation extends DonateAmountConversation
{
    /**
     * Get the product to use for the donation.
     *
     * @return \Ds\Models\Product
     */
    public function getProduct(): Product
    {
        return $this->getFundraisingPage()->product;
    }

    /**
     * Get the fundraising page.
     *
     * @return \Ds\Models\FundraisingPage
     */
    public function getFundraisingPage(): FundraisingPage
    {
        $fundraisingPageId = $this->getConversation()->metadata['fundraising_page'];

        // When BotMan serializes the Conversation having references to Eloquent
        // models can cause issues when SuperClosure serializes the closure scopes
        return reqcache("botman-conversation:fundraising-page-{$fundraisingPageId}", function () use ($fundraisingPageId) {
            return FundraisingPage::websiteType()->findOrFail($fundraisingPageId);
        });
    }

    /**
     * Add an item to the cart.
     *
     * @param \Ds\Models\Order $cart
     * @param array $item
     */
    protected function addItemToCart(Order $cart, array $item)
    {
        $fundraiser = $this->getFundraisingPage();

        $item['fundraising_page_id'] = $fundraiser->id;
        $item['fundraising_member_id'] = $fundraiser->member_organizer_id;

        parent::addItemToCart($cart, $item);
    }

    /**
     * Fired when the cart is completed.
     *
     * @param \Ds\Models\Order $cart
     */
    protected function onCartCompeted(Order $cart)
    {
        parent::onCartCompeted($cart);

        $fundraiser = $this->getFundraisingPage()->refresh();

        $this->say(sprintf(
            '%s has now raised %s of their %s goal! 🎉',
            $fundraiser->title,
            money($fundraiser->amount_raised, $fundraiser->currency_code),
            money($fundraiser->goal_amount, $fundraiser->currency_code)
        ));
    }

    /**
     * Get the configuration for the conversation.
     *
     * @return array
     */
    public static function configuration(): array
    {
        return [
            'label' => 'Donation with an amount (towards fundraising page)',
            'example' => 'DONATE {amount}',
            'parameters' => [
                'amount' => static::AMOUNT_PARAMETER_REGEX,
            ],
            'settings' => DataAccess::collection([
                [
                    'type' => 'header',
                    'content' => 'Options',
                ], [
                    'type' => 'fundraising_page',
                    'name' => 'fundraising_page',
                    'label' => 'Fundraising Page',
                ],
            ]),
        ];
    }
}
