<?php

namespace Ds\Domain\Commerce\Contracts;

interface ProvidesTokenId
{
    /**
     * Get a token id.
     */
    public function getTokenId(): string;
}
