<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\Media */
class MediaResource extends JsonResource
{
    /** @var array */
    protected $custom = [];

    /** @var string|null */
    protected $thumb = null;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->hashid,
            'full' => $this->public_url,
            'thumb' => $this->thumb ?? $this->thumbnail_url,
            'custom' => $this->when(count($this->custom), $this->custom),
            'is_audio' => $this->is_audio,
            'is_image' => $this->is_image,
            'is_video' => $this->is_video,
            'created_at' => fromUtcFormat($this->created_at, 'api'),
            'updated_at' => fromUtcFormat($this->updated_at, 'api'),
        ];
    }

    public function setCustom(array $custom): self
    {
        $this->custom = array_map(fn ($options) => $this->getImageUrl((array) $options), $custom);

        return $this;
    }

    /**
     * @param array|string $thumb
     * @return $this
     */
    public function setThumb($thumb): self
    {
        $this->thumb = $this->getImageUrl((array) $thumb);

        return $this;
    }
}
