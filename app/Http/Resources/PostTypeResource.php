<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\PostType */
class PostTypeResource extends JsonResource
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
            'url_slug' => $this->url_slug,
            'name' => $this->name,
            'sysname' => $this->sysname,
            'template_suffix' => $this->template_suffix,
            'default_template_suffix' => $this->default_template_suffix,
            'rss_link' => $this->rss_link,
            'rss_copyright' => $this->rss_copyright,
            'rss_description' => $this->rss_description,
            'imagepath' => $this->imagepath,
            'show_social_share_links' => $this->show_social_share_links,
            'photo' => MediaResource::make($this->photo),
            'created_at' => fromUtcFormat($this->created_at, 'api'),
            'updated_at' => fromUtcFormat($this->updated_at, 'api'),
        ];
    }
}
