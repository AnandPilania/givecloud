<?php

namespace Ds\Domain\Commerce\Exceptions;

class Exception extends \Exception
{
    /**
     * Create an instance.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable $previous
     */
    public function __construct(string $message, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, (int) $code, $previous);
    }
}
