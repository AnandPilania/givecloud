<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class PostDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'handle' => $source->url_slug,
            'title' => $source->name,
            'excerpt' => $source->description,
            'content' => do_shortcode($source->body ?? ''),
            'misc_1' => $source->misc1,
            'misc_2' => $source->misc2,
            'misc_3' => $source->misc3,
            'created_at' => $source->created_at,
            'published_at' => $source->postdatetime,
            'unpublish_at' => $source->expirydatetime,
            'sequence' => $source->sequence,
            'url' => ($source->postType->sysname === 'slide') ? $source->url : $source->absolute_url,
            'share_url' => $source->share_url,
            'type_name' => $source->postType->name,
            'type_handle' => $source->postType->absolute_url,
            'show_sharing_links' => $source->postType->show_social_share_links,
        ];
    }

    public function author()
    {
        return new AuthorDrop($this->source->createdBy);
    }

    public function feature_image()
    {
        return $this->source->featuredImage ?? $this->source->enclosure ?? null;
    }

    public function alternate_image()
    {
        return $this->source->altImage ?? null;
    }

    public function categories()
    {
        return Drop::collectionFactory($this->source->categories, 'PostTypeCategory', [
            'posts' => [],
        ]);
    }

    public function metadata()
    {
        return $this->source->metadata;
    }

    public function template()
    {
        $suffix = $this->source->template_suffix ?? $this->source->postType->default_template_suffix;

        return Drop::factory(rtrim("post.$suffix", '.'), 'Template');
    }
}
