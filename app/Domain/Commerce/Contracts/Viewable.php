<?php

namespace Ds\Domain\Commerce\Contracts;

interface Viewable
{
    /**
     * Get view data for the gateway.
     *
     * @return string
     */
    public function getView(): string;

    /**
     * Get view configuration for the gateway.
     *
     * @return object|null
     */
    public function getViewConfig(): ?object;
}
