<?php

namespace Ds\Domain\Commerce\Responses;

class RedirectToResponse extends UrlResponse
{
    /** @var string */
    protected $type = 'redirect_to';
}
