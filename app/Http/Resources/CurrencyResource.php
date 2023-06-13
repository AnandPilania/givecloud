<?php

namespace Ds\Http\Resources;

use Ds\Domain\Commerce\Currency;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Domain\Commerce\Currency */
class CurrencyResource extends JsonResource
{
    public function __construct(Currency $currency = null)
    {
        $this->resource = $currency ?? currency();
    }

    /**
     * Transform the rethis into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'iso_code' => $this->code,
            'symbol' => $this->symbol,
            'rate' => $this->rate,
        ];
    }
}
