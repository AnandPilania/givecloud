<?php

namespace Ds\Http\Resources\Settings;

use Ds\Http\Resources\MediaResource;
use Ds\Illuminate\Http\Resources\Json\JsonResource;
use Ds\Models\Media;

class BrandingResource extends JsonResource
{
    public function __construct()
    {
        $this->resource = new \stdClass;
    }

    public function toArray($request): array
    {
        return [
            'org_logo' => $this->getOrgLogo(),
            'org_primary_color' => sys_get('org_primary_color') ?: sys_get('default_color_1') ?: null,
        ];
    }

    private function getOrgLogo(): ?MediaResource
    {
        $media = sys_get('org_logo');

        /* Fallback for legacy org_logo which was stored as string */
        if (is_string($media)) {
            $media = Media::findByUrl(sys_get('org_logo'));
        }

        $media ??= Media::findByUrl(sys_get('default_logo'));

        return $media ? MediaResource::make($media)->setThumb(['300x', 'crop' => 'entropy']) : null;
    }
}
