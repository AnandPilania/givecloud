<?php

namespace Ds\Domain\Commerce\Shipping;

use Illuminate\Support\Collection;

abstract class AbstractCarrier
{
    /**
     * Get the carrier handle.
     *
     * @return string
     */
    abstract public function getHandle(): string;

    /**
     * Get the carrier name.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Get shipping rates.
     *
     * @param \Ds\Domain\Commerce\Shipping\ShipmentOptions $options
     * @return \Illuminate\Support\Collection
     */
    abstract public function getRates(ShipmentOptions $options): Collection;
}
