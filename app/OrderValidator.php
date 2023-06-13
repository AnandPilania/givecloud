<?php

namespace Ds;

use Ds\Domain\Commerce\Currency;
use Ds\Domain\Commerce\Enums\ContributionPaymentType;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Order;
use Illuminate\Support\Str;

class OrderValidator
{
    /** @var \Ds\Models\Order */
    protected $order;

    /**
     * Create an instance.
     *
     * #param \Ds\Models\Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->order->loadMissing('items.variant.product');
    }

    /**
     * Validate a dynamic validation.
     */
    public function validate($validation)
    {
        $validation = 'validate' . Str::studly($validation);

        if (method_exists($this, $validation)) {
            return $this->{$validation}();
        }

        return false;
    }

    /**
     * Ensure the presence of an active currency code.
     */
    public function validateCurrency()
    {
        $currency = new Currency($this->order->currency_code);

        if (! $currency->active) {
            throw new MessageException('Unsupported local currency.');
        }
    }

    /**
     * Ensure the presence of payment method on the order.
     */
    public function validateUnpaid()
    {
        if ($this->order->is_paid) {
            throw new MessageException('This contribution has already been charged.');
        }
    }

    /**
     * Ensure the presence of payment provider on the order.
     */
    public function validatePresenceOfPaymentProvider()
    {
        if (! $this->order->payment_provider_id) {
            throw new MessageException('Please select a payment method.');
        }
    }

    /**
     * Ensure the presence of payment method on the order.
     */
    public function validatePresenceOfPaymentMethod()
    {
        if (! $this->order->payment_method_id) {
            throw new MessageException('Please select a payment method.');
        }
    }

    /**
     * Ensure the presence of items in the order.
     */
    public function validatePresenceOfItems()
    {
        if ($this->order->items->count() === 0) {
            throw new MessageException('There are no items in your contribution.');
        }
    }

    /**
     * Ensure presence of the required billing information.
     */
    public function validatePresenceOfBillingInformation()
    {
        if ($this->order->is_pos || $this->order->source === 'Kiosk') {
            return true;
        }

        if ($this->order->fundraisingForm) {
            if ($this->order->payment_type === ContributionPaymentType::PAYPAL) {
                return true;
            }

            if (! $this->order->fundraisingForm->require_billing_address && $this->order->payment_type !== ContributionPaymentType::BANK_ACCOUNT) {
                return true;
            }
        }

        if ($this->order->payment_type === ContributionPaymentType::WALLET_PAY) {
            return true;
        }

        $missing = collect([
            $this->order->billing_first_name,
            $this->order->billing_last_name,
            $this->order->billingemail,
            $this->order->billingzip,
            $this->order->billingcountry,
        ])->filter(function ($value) {
            return empty($value);
        })->count();

        if ($missing) {
            throw new MessageException('Please provide required billing information.');
        }
    }

    /**
     * Ensure the presence of an organization name.
     */
    public function validatePresenceOfOrganizationName()
    {
        if (! $this->order->accountType || ! $this->order->accountType->is_organization) {
            return;
        }

        if (empty($this->order->billing_organization_name)) {
            throw new MessageException('Please provide your organization name.');
        }
    }

    /**
     * Ensure the presence of a member.
     */
    public function validatePresenceOfMember()
    {
        if ($this->order->member_id) {
            return;
        }

        if ($this->order->is_pos) {
            return;
        }

        if ($this->order->isForFundraisingForm()) {
            return;
        }

        if ($this->order->recurring_items && sys_get('rpp_require_login') && $this->order->source !== 'Kiosk') {
            throw new MessageException('Your recurring transaction(s) must have a supporter account associated with it. Please login or sign-up by providing a password and try your transaction again.');
        }

        if (is_instanceof($this->order->paymentProvider, \Ds\Domain\Commerce\Gateways\GoCardlessGateway::class)) {
            throw new MessageException('Your bank transaction(s) require a supporter login associated with it. Please login or sign-up by providing a password and try your transaction again.');
        }
    }

    /**
     * Ensure the the minimum order total is met.
     */
    public function validateMinimumOrderTotal()
    {
        $minAmount = money(sys_get('double:checkout_min_value'))
            ->toCurrency($this->order->currency_code)
            ->getAmount();

        if (empty($this->order->totalamount) || $this->order->totalamount >= $minAmount) {
            return;
        }

        throw new MessageException(sprintf(
            'Your purchase must be a minimum of %s.',
            money($minAmount, $this->order->currency_code)
        ));
    }

    /**
     * Ensure all the required shipping information is present.
     */
    public function validateShippingRequirements()
    {
        if (! feature('shipping')) {
            return;
        }

        if ($this->order->is_pos || $this->order->source === 'Kiosk') {
            return true;
        }

        if ($this->order->shippable_items === 0) {
            return;
        }

        if ($this->order->is_free_shipping) {
            return;
        }

        if ($this->order->total_weight === 0.0) {
            return;
        }

        if (! $this->order->is_shipping_address_valid) {
            throw new MessageException('Please provide your shipping information.');
        }

        if ($this->order->courier_method || $this->order->shipping_method_id) {
            return;
        }

        throw new MessageException('Please select an available shipping method.');
    }

    /**
     * Check the per account promos.
     */
    public function validatePerAccountPromos()
    {
        $errors = $this->order->revalidatePerAccountPromos();

        if (count($errors) > 0) {
            throw new MessageException(implode("\n", $errors));
        }
    }

    /**
     * Check the available inventory for the items in the order.
     */
    public function validateItemAvailability()
    {
        $this->order->items
            ->filter(function ($item) {
                return isset($item->variant->product);
            })->map(function ($item) {
                return $item->variant()
                    ->select('productinventory.*')
                    ->first();
            })->reject(function ($variant) {
                return $variant->product->outofstock_allow;
            })->each(function ($variant) {
                $productName = $variant->product->name;

                if ($variant->variantname) {
                    $productName = "$productName ($variant->variantname)";
                }

                if ($variant->quantity === 0) {
                    throw new MessageException(sprintf(
                        "There is no more of '%s' available.",
                        e($productName)
                    ));
                }

                $total = $this->order->items
                    ->filter(function ($item) use ($variant) {
                        return $item->productinventoryid === $variant->id;
                    })->sum('qty');

                if ($variant->quantity < $total) {
                    throw new MessageException(sprintf(
                        "There is insufficient availability for '%s' (only %s remaining).",
                        e($productName),
                        $variant->quantity
                    ));
                }
            });
    }

    /**
     * Check the sales limit for the items in the order.
     */
    public function validateSalesLimits()
    {
        $this->order->items
            ->reject(function ($item) {
                return empty($item->variant->product->limit_sales);
            })->groupBy('variant.product.id')
            ->each(function ($items) {
                $total = $items->sum('qty');

                $product = $items[0]->variant->product;
                $available_for_purchase = $product->available_for_purchase;

                if ($product->available_for_purchase < $total) {
                    if ($available_for_purchase) {
                        throw new MessageException(sprintf(
                            "There is insufficient availability for '%s' (only %s remaining).",
                            e($product->name),
                            $available_for_purchase
                        ));
                    }

                    throw new MessageException(sprintf(
                        "There is no more of '%s' available.",
                        e($product->name)
                    ));
                }
            });
    }

    /**
     * Check for any ACH requirements.
     */
    public function validateAchRequirements()
    {
        $requiresAch = $this->order->items->reduce(function ($carry, $item) {
            return $carry || $item->requires_ach;
        });

        if ($requiresAch && $this->order->payment_type !== 'bank_account') {
            throw new MessageException('There is an item in your cart that requires payment via ACH.');
        }
    }

    /**
     * Check the billing country against the IP country.
     */
    public function validateBillingCountryMatchesIp()
    {
        if ($this->order->is_pos) {
            return;
        }

        if (in_array($this->order->payment_type, ['paypal', 'wallet_pay'], true)) {
            return;
        }

        if (sys_get('require_ip_country_match') && $this->order->billingcountry !== $this->order->ip_country) {
            throw new MessageException("Your billing country doesn't match your IP address.");
        }
    }

    /**
     * Check the number of authorization attempts.
     */
    public function validateAuthorizationAttempts()
    {
        if ($this->order->auth_attempts >= sys_get('ss_auth_max_attempts')) {
            throw new MessageException('Maximum number of authorization attempts exceeded.');
        }
    }
}
