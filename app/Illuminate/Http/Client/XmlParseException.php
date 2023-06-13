<?php

namespace Ds\Illuminate\Http\Client;

use Exception;
use LibXMLError;
use Throwable;

class XmlParseException extends Exception
{
    /** @var \LibXMLError */
    protected $error;

    public function __construct(
        string $message,
        Throwable $previous = null,
        LibXMLError $error = null
    ) {
        $this->error = $error;

        parent::__construct($message, 0, $previous);
    }

    public function getError(): ?LibXMLError
    {
        return $this->error;
    }
}
