<?php

namespace Ds\Illuminate\Http\Resources\Json;

use Illuminate\Http\Resources\Json\JsonResource as IlluminateJsonResource;

class JsonResource extends IlluminateJsonResource
{
    public function toObject(): \stdClass
    {
        return json_decode($this->toJson());
    }
}
