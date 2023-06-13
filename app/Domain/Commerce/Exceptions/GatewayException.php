<?php

namespace Ds\Domain\Commerce\Exceptions;

use Ds\Domain\Shared\Exceptions\DisclosableException;

class GatewayException extends Exception implements DisclosableException
{
    /** @var mixed */
    protected $response;

    /**
     * Create an instance.
     *
     * @param string $message
     * @param int|string $code
     * @param \Throwable|null $previous
     * @param mixed $response
     */
    public function __construct($message, $code = 422, $previous = null, $response = null)
    {
        $this->setResponse($response);

        parent::__construct((string) $message, 0, $previous);

        $this->code = $code;
    }

    /**
     * Get the response.
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the response.
     *
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Return the string representation of the exception.
     *
     * @return string
     */
    public function __toString()
    {
        $out = $this->getMessage();

        if ($this->getCode()) {
            $out .= ' (Code: ' . $this->getCode() . ')';
        }

        return $out;
    }
}
