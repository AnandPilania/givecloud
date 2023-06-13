<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ShippingMethodDrop extends Drop
{
    /**
     * Create an instance.
     *
     * @param array $source
     */
    public function __construct(array $source)
    {
        $this->source = (object) [
            'name' => Arr::get($source, 'name'),
            'title' => Arr::get($source, 'title'),
            'cost' => Arr::get($source, 'cost', 0),
            'courier' => Arr::get($source, 'courier'),
            'shipping_method_id' => Arr::get($source, 'shipping_method_id'),
            'free_shipping' => Arr::get($source, 'free', false),
        ];

        $this->liquid = [
            'title' => $this->source->title,
            'price' => $this->source->cost,
            'original_price' => $this->source->cost,
            'courier' => $this->source->courier,
        ];
    }

    public function handle()
    {
        return Str::slug($this->source->name) . '-' . number_format($this->source->cost, 2, '', '');
    }

    public function value()
    {
        if ($this->source->free_shipping) {
            return '';
        }

        return $this->source->shipping_method_id ?: $this->source->title;
    }
}
