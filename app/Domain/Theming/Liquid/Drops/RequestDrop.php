<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Throwable;

class RequestDrop extends Drop
{
    /** @var \Illuminate\Http\Request */
    protected $request;

    /**
     * Create an instance.
     */
    public function __construct()
    {
        $this->request = request();

        $this->liquid = [
            'host' => $this->request->getHost(),
            'path' => $this->request->path(),
            'canonical_url' => $this->request->url(),
            'referer' => $this->request->server('HTTP_REFERER') ?: null,
            'data' => session('liquid_req') ?? [],
        ];
    }

    public function location()
    {
        try {
            return app('geoip')->getLocationData($this->request->ip());
        } catch (Throwable $e) {
            return new EmptyDrop;
        }
    }

    public function input()
    {
        return $this->escape($this->request->all());
    }

    public function old(): array
    {
        return $this->escape($this->request->old(null, []), false);
    }

    protected function escape(array $values, bool $stripTags = true): array
    {
        foreach ($values as $name => &$value) {
            if (preg_match('/[^a-z0-9_-]/i', $name)) {
                continue;
            }

            if (is_array($value) || is_object($value)) {
                $value = null;
            } elseif ($value === 'true') {
                $value = true;
            } elseif ($value === 'false') {
                $value = false;
            } elseif (is_numeric($value)) {
                $value = (float) $value;
            } elseif ($stripTags) {
                $value = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
            } else {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }

        return $values;
    }
}
