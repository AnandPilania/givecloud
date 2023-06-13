<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $code
 * @property-read float $price
 * @property-read float $rate
 */
class TaxLineResource extends JsonResource
{
    /**
     * Transform the rethis into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'title' => $this->code,
            'price' => $this->price,
            'rate' => round($this->rate / 100, 4),
            'rate_percentage' => $this->rate,
        ];
    }
}
