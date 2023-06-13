<?php

namespace Ds\Domain\Commerce\Responses;

use Ds\Domain\Commerce\Contracts\GatewayResponse;

class UrlResponse implements GatewayResponse
{
    /** @var string */
    protected $type = 'url';

    /** @var string */
    protected $url;

    /** @var array|null */
    protected $data;

    /**
     * Create an instance.
     *
     * @param string $url
     * @param array|null $data
     */
    public function __construct(string $url, ?array $data = null)
    {
        $this->url = $url;
        $this->data = $data;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        if ($this->data) {
            return [
                'type' => $this->type,
                'url' => $this->url,
                'data' => $this->data,
            ];
        }

        return [
            'type' => $this->type,
            'url' => $this->url,
        ];
    }

    /**
     * Return a string representation of the URL.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->url;
    }
}
