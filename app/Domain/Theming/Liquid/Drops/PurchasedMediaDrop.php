<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class PurchasedMediaDrop extends Drop
{
    protected function initialize($source)
    {
        parent::initialize($source);

        $variant = $source->item->variant;
        $product = $variant->product;

        $this->liquid += [
            'id' => $source->id,
            'download_url' => ($source->expired) ? null : $source->url,
            'external_resource_uri' => ($source->expired) ? null : $source->external_resource_uri,
            'description' => $source->description,
            'variant' => $variant,
            'order_number' => $source->item->order->client_uuid,
            'product' => $product,
            'expired' => $source->expired,
            'days_left' => $source->days_left,
            'granted' => $source->granted,
            'expiration_time' => $source->expiration_time,
            'title' => $product->name . (($variant->variantname != 'Default Option') ? ' - ' . $variant->variantname : ''),
        ];
    }

    public function oembed()
    {
        return ($this->source->expired) ? null : oembed_get($this->source->external_resource_uri);
    }
}
