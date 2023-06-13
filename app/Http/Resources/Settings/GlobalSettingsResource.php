<?php

namespace Ds\Http\Resources\Settings;

use Ds\Illuminate\Http\Resources\Json\JsonResource;

class GlobalSettingsResource extends JsonResource
{
    public function __construct()
    {
        $this->resource = new \stdClass;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(
            BrandingResource::make()->toArray($request),
            FundraisingResource::make()->toArray($request),
            OrganizationResource::make()->toArray($request)
        );
    }
}
