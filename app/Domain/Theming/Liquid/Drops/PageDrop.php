<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class PageDrop extends Drop
{
    protected $attributes = ['id'];

    protected function initialize($source)
    {
        $this->liquid = [
            'title' => $source->pagetitle ?: $source->title,
            'url' => $source->url ?: null,
            'share_url' => $source->share_url,
            'template_suffix' => $source->template_suffix,
            'page_description' => $source->metadescription ?? '',
            'page_keywords' => $source->metakeywords ?? '',
        ];
    }

    public function content()
    {
        $content = $this->source->body ?? '';

        if ($this->source->type === 'liquid') {
            $content = liquid($content, ['page' => $this], "page_drop:{$this->source->id}");
        }

        return do_shortcode($content);
    }

    public function parent()
    {
        return $this->source->parent;
    }

    public function feature_image()
    {
        return $this->source->featuredImage ?? null;
    }

    public function alternate_image()
    {
        return $this->source->altImage ?? null;
    }

    public function metadata()
    {
        return $this->source->metadata;
    }
}
