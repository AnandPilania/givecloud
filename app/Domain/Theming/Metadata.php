<?php

namespace Ds\Domain\Theming;

use Illuminate\Support\Str;
use Opis\JsonSchema\Loaders\File;
use Opis\JsonSchema\Validator;

class Metadata
{
    /** @var \Opis\JsonSchema\Validator */
    protected $validator;

    /** @var array */
    protected $templateMapping = [
        'collection' => \Ds\Models\ProductCategory::class,
        'page' => \Ds\Models\Node::class,
        'post-type' => \Ds\Models\PostType::class,
        'post' => \Ds\Models\Post::class,
        'product' => \Ds\Models\Product::class,
    ];

    /** @var \Ds\Domain\Theming\Theme */
    protected $theme;

    /**
     * Create an instance.
     *
     * @param \Ds\Domain\Theming\Theme $theme
     */
    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
    }

    /**
     * Get the metadata for a given template.
     *
     * @param string $name
     * @return \Ds\Domain\Theming\MetadataTemplate[]
     */
    public function getTemplateMetadata($name)
    {
        $templates = $this->theme->getAssetList("templates/$name.*")->sortBy(function ($template) {
            return Str::before($template, '.liquid');
        })->toArray();

        return collect(array_combine(
            array_values($templates),
            array_values($templates)
        ))->mapWithKeys(function ($template) use ($name) {
            return [preg_replace("#templates/$name\.?(.*)\.liquid#", '$1', $template) => $template];
        })->map(function ($template, $suffix) {
            return new MetadataTemplate($suffix, $template, $this->theme, $this->getValidator());
        })->toArray();
    }

    /**
     * Get the content editor classes.
     *
     * @param \Ds\Domain\Theming\MetadataTemplate[] $templates
     * @return string
     */
    public function getContentEditorClasses(array $templates)
    {
        $classes = ['hide'];

        foreach ($templates as $template) {
            if (! $template->config->hide_content_editor) {
                $classes = array_merge($classes, explode(' ', $template->classes));
            }
        }

        return implode(' ', array_unique($classes));
    }

    /**
     * Get the JSON validator.
     *
     * @return \Opis\JsonSchema\Validator
     */
    private function getValidator(): Validator
    {
        if (! $this->validator) {
            $loader = new File(
                'http://theming.givecloud.co/schemas',
                [base_path('resources/theming/schemas')]
            );

            $this->validator = new Validator(null, $loader);
        }

        return $this->validator;
    }
}
