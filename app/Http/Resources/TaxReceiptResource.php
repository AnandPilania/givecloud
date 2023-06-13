<?php

namespace Ds\Http\Resources;

use Ds\Models\TaxReceipt;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TaxReceipt
 */
class TaxReceiptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'number' => $this->number,
            'amount' => $this->amount,
            'currency_code' => $this->currency_code,
            'issued_at' => fromUtcFormat($this->issued_at, 'api'),
            'issued_to' => $this->name ?? '(blank)',
            'is_voided' => (bool) $this->status === 'void',
        ];
    }
}
