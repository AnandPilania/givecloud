<?php

namespace Ds\Domain\DoubleTheDonation\Http\Resources;

use Ds\Domain\DoubleTheDonation\Enums\Status;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordResource extends JsonResource
{
    public function toArray($request): array
    {
        return array_merge($this->resource, [
            'status_label' => Status::label(data_get($this->resource, 'status')),
        ]);
    }
}
