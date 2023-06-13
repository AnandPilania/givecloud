<?php

namespace Ds\Domain\Commerce\Responses;

class ErrorResponse extends UrlResponse
{
    /**
     * Create an instance.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->data = $message;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'error',
            'error' => $this->data,
        ];
    }

    /**
     * Return a string representation of the URL.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->data;
    }
}
