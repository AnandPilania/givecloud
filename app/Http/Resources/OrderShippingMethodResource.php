<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin \Ds\Models\Order */
class OrderShippingMethodResource extends JsonResource
{
    /**
     * Transform the rethis into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $titleNameAndCourier = $this->getTitleNameAndCourier();

        return array_merge(
            $titleNameAndCourier,
            [
                'price' => $this->shipping_amount ?: 0,
                'handle' => Str::slug($titleNameAndCourier['name']) . '-' . number_format($this->shipping_amount ?: 0, 2, '', ''),
                'value' => $this->shipping_method_id ?: $titleNameAndCourier['title'],
            ],
            $this->freeShippingOverrides()
        );
    }

    private function freeShippingOverrides(): array
    {
        if (! $this->is_free_shipping) {
            return [];
        }

        return ['title' => 'FREE shipping', 'value' => ''];
    }

    private function getTitleNameAndCourier(): array
    {
        if ($this->shippingMethod) {
            return [
                'title' => $this->shippingMethod->name,
                'name' => $this->shippingMethod->name,
                'courier' => null,
            ];
        }

        if ($this->courier_method) {
            preg_match('/^(?<courier>.+): (?<name>.+)$/', $this->courier_method, $matches);

            return [
                'title' => $this->courier_method,
                'courier' => $matches['courier'] ?: null,
                'name' => $matches['name'] ?: null,
            ];
        }

        return [
            'name' => $this->name ?? '',
            'title' => $this->title ?? '',
            'courier' => $this->courier ?? '',
        ];
    }
}
