<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\Post */
class PostResource extends JsonResource
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
            'id' => $this->hashid,
            'name' => $this->name ?: null,
            'url' => $this->this_url ?: null,
            'absolute_url' => $this->absolute_url ?: null,
            'summary' => $this->description ?: null,
            'feature_image' => MediaResource::make($this->featuredImage),
            'alt_image' => MediaResource::make($this->altImage),
            'tags' => collect(explode(',', $this->tags))->map('trim')->all(),
            'body' => $this->body ?: null,
            'published_on' => fromUtcFormat($this->postdatetime, 'api'),
            'expires_on' => fromUtcFormat($this->expirydatetime, 'api'),
            // Relationships
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'post_type' => PostTypeResource::make($this->whenLoaded('postType')),
        ];
    }
}
