<?php

namespace Ds\Domain\Commerce\Responses;

class JsonpResponse extends UrlResponse
{
    /** @var string */
    protected $type = 'jsonp';
}
