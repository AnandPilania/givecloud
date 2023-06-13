<?php

namespace Ds\Common\DonorPerfect;

use Exception;
use Illuminate\Http\Client\RequestException as HttpClientRequestException;
use Illuminate\Http\Client\Response;

class RequestException extends HttpClientRequestException
{
    public function __construct(string $message, Response $response)
    {
        Exception::__construct($message, $response->status());

        $this->response = $response;
    }
}
