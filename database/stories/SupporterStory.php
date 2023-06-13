<?php

namespace Database\Stories;

use Database\Factories\PaymentMethodFactory;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Eloquent\Story;
use Ds\Models\Member as Supporter;
use Ds\Models\PaymentMethod;

/**
 * @method \Ds\Models\Member|array<Supporter> create()
 */
class SupporterStory extends Story
{
    /** @var \Database\Factories\MemberFactory */
    protected $supporterFactory;

    /** @var \Database\Factories\PaymentMethodFactory[] */
    protected $paymentMethodFactories = [];

    /** @var \Ds\Domain\Commerce\Models\PaymentProvider|null */
    protected $paymentProvider = null;

    public function __construct(Supporter $supporter = null)
    {
        $this->supporterFactory = $supporter ?? Supporter::factory()->individual();
    }

    /**
     * @return static
     */
    public function tokenizingPaymentMethod(array $attributes = []): self
    {
        return $this->withPaymentMethod(PaymentMethod::factory()->tokenizing()->state($attributes));
    }

    /**
     * @return static
     */
    public function usingPaymentProvider(?PaymentProvider $paymentProvider): self
    {
        $this->paymentProvider = $paymentProvider;

        return $this;
    }

    /**
     * @return static
     */
    public function withBankAccount(): self
    {
        return $this->withPaymentMethod(PaymentMethod::factory()->bankAccount());
    }

    /**
     * @return static
     */
    public function withCreditCard(array $attributes = []): self
    {
        return $this->withPaymentMethod(PaymentMethod::factory()->creditCard()->state($attributes));
    }

    /**
     * @return static
     */
    public function withPaymentMethod(?PaymentMethodFactory $factory = null): self
    {
        if (empty($factory)) {
            $factory = PaymentMethod::factory()->creditCard();
        }

        $this->paymentMethodFactories[] = $factory;

        return $this;
    }

    protected function execute(): Supporter
    {
        $supporter = $this->supporterFactory->create();

        $this->createPaymentMethods($supporter);

        return $supporter;
    }

    protected function createPaymentMethods(Supporter $supporter): void
    {
        if (count($this->paymentMethodFactories) === 0) {
            return;
        }

        array_walk($this->paymentMethodFactories, function ($factory) use ($supporter) {
            return $factory->for($supporter, 'member')
                ->when($this->paymentProvider, function ($factory) {
                    return $factory->afterCreating(function (PaymentMethod $paymentMethod) {
                        $paymentMethod->payment_provider_id = $this->paymentProvider->getKey();
                        $paymentMethod->save();
                    });
                })->create();
        });

        $supporter->paymentMethods()->first()->useAsDefaultPaymentMethod();
    }
}
