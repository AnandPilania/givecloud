<?php

namespace Ds\Domain\Commerce\Responses;

class JsonResponse extends UrlResponse
{
    /** @var string */
    protected $type = 'json';

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
