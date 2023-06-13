<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class PostTypeDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'handle' => $source->url_slug,
            'share_url' => $source->share_url,
            'name' => $source->name,
            'media_type' => $source->sysname,
            'rss' => [
                'copyright' => $source->rss_copyright,
                'link' => $source->absolute_url,
                'description' => $source->rss_description,
            ],
        ];
    }

    public function feature_image()
    {
        return $this->source->photo ?? null;
    }

    public function categories()
    {
        return $this->source
            ->categories()
            ->active()
            ->whereNull('parent_id')
            ->orderBy('sequence')
            ->get();
    }

    public function metadata()
    {
        return $this->source->metadata;
    }
}
