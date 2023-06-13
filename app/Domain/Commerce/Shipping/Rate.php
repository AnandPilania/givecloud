<?php

namespace Ds\Domain\Commerce\Shipping;

use Ds\Domain\Commerce\Money;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class Rate implements Arrayable, JsonSerializable
{
    /** @var string */
    private $carrier;

    /** @var string */
    private $code;

    /** @var string */
    private $name;

    /** @var string */
    private $title;

    /** @var \Ds\Domain\Commerce\Money */
    private $amount;

    /**
     * Create an instance.
     *
     * @param \Ds\Domain\Commerce\Shipping\AbstractCarrier $carrier
     * @param string $code
     * @param string $name
     * @param \Ds\Domain\Commerce\Money $amount
     */
    public function __construct(AbstractCarrier $carrier, $code, $name, Money $amount)
    {
        $this->setName($name, $code, $carrier);
        $this->setAmount($amount);
    }

    /**
     * Sets the name, code, title and carrier.
     *
     * @param string $name
     * @param string $code
     * @param \Ds\Domain\Commerce\Shipping\AbstractCarrier $carrier
     * @return self
     */
    public function setName($name, $code, AbstractCarrier $carrier): self
    {
        $this->code = $code;
        $this->name = strip_tags($name);
        $this->title = $carrier->getName() . ": {$this->name}";
        $this->carrier = $carrier->getHandle();

        return $this;
    }

    /**
     * Sets the amount.
     *
     * @param \Ds\Domain\Commerce\Money $amount
     * @return self
     */
    public function setAmount(Money $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get private property.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'carrier':       return $this->carrier;
            case 'code':          return $this->code;
            case 'name':          return $this->name;
            case 'title':         return $this->title;
            case 'amount':        return $this->amount->getAmount();
            case 'currency_code': return $this->amount->getCurrencyCode();
        }
    }

    /**
     * Convert to a different currency.
     *
     * @param mixed $currencyCode
     * @return \Ds\Domain\Commerce\Shipping\Rate
     */
    public function toCurrency($currencyCode)
    {
        return (clone $this)
            ->setAmount($this->amount->toCurrency($currencyCode));
    }

    /**
     * Get the Rate as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'carrier' => $this->carrier,
            'code' => $this->code,
            'name' => $this->name,
            'amount' => $this->amount->getAmount(),
            'currency_code' => $this->amount->getCurrencyCode(),
        ];
    }

    /**
     * Specify data which should be serialized.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
