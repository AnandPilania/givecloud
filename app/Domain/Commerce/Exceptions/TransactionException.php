<?php

namespace Ds\Domain\Commerce\Exceptions;

use Ds\Domain\Commerce\Responses\TransactionResponse;

class TransactionException extends GatewayException
{
    /** @var \Ds\Domain\Commerce\Responses\TransactionResponse */
    protected $response;

    /**
     * Create an instance.
     *
     * @param \Ds\Domain\Commerce\Responses\TransactionResponse $response
     */
    public function __construct(TransactionResponse $response)
    {
        $this->setResponse($response);

        parent::__construct((string) $this);
    }

    /**
     * Get the response.
     *
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the response.
     *
     * @param \Ds\Domain\Commerce\Responses\TransactionResponse $response
     */
    public function setResponse($response)
    {
        if ($response instanceof TransactionResponse) {
            $this->response = $response;
        }
    }

    /**
     * Return the string representation of the exception.
     *
     * @return string
     */
    public function __toString()
    {
        $out = $this->response->getResponseText();

        if ($this->response->getTransactionId()) {
            $out .= ' (ID: ' . $this->response->getTransactionId() . ')';
        }

        return $out;
    }
}
