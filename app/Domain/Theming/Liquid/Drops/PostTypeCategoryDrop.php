<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class PostTypeCategoryDrop extends Drop
{
    /** @var array */
    protected $serializationBlacklist = ['parent_category'];

    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'name' => $source->name,
            'handle' => $source->handle,
            'description' => $source->description,
        ];
    }

    public function feature_image()
    {
        return $this->source->photo ?? null;
    }

    public function posts()
    {
        return Drop::collectionFactory($this->source->posts, 'Post', [
            'categories' => [],
        ]);
    }

    public function parent_category()
    {
        return Drop::factory($this->source->parentCategory, 'PostTypeCategory', $this->overrides);
    }

    public function sub_categories()
    {
        return Drop::collectionFactory($this->source->childCategories, 'PostTypeCategory', $this->overrides);
    }

    public function metadata()
    {
        return $this->source->metadata;
    }
}
