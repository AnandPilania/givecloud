<?php

namespace Ds\Domain\Shared\Exceptions;

use DomainException;

class MessageException extends DomainException implements DisclosableException
{
    /** @var array */
    protected $context = [];

    /**
     * Get context data.
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set context data.
     *
     * @param array $context
     * @return $this
     */
    public function setContext(array $context): MessageException
    {
        $this->context = $context;

        return $this;
    }
}
