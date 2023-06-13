<?php

namespace Ds\Domain\Theming;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Opis\JsonSchema\Validator;

/**
 * @property-read string $suffix
 * @property-read \stdClass $config
 * @property-read array $schema
 * @property-read string $slug
 * @property-read string $classes
 */
class MetadataTemplate
{
    /** @var string */
    private $suffix;

    /** @var \stdClass */
    private $config;

    /** @var array */
    private $schema;

    /**
     * The template config values.
     *
     * @var array
     */
    private $templateConfig = [
        'hide_content_editor' => false,
    ];

    /**
     * @param string $suffix
     * @param string $template
     * @param \Ds\Domain\Theming\Theme $theme
     * @param \Opis\JsonSchema\Validator $validator
     */
    public function __construct(string $suffix, string $template, Theme $theme, Validator $validator = null)
    {
        $this->suffix = $suffix;
        $this->setSchema($template, $theme, $validator);
    }

    /**
     * Set the schema.
     *
     * @param string $template
     * @param \Ds\Domain\Theming\Theme $theme
     * @param \Opis\JsonSchema\Validator $validator
     */
    public function setSchema(string $template, Theme $theme, Validator $validator = null)
    {
        $schema = $this->extractSchemaFromTemplate($template, $theme, $validator);

        foreach ($schema as $data) {
            $data->slug = $this->slug . '-' . Str::slug($data->name);
        }

        $this->schema = $schema;
        $this->setConfig();
    }

    /**
     * Extract contents of schema tag from template.
     *
     * @param string $template
     * @param \Ds\Domain\Theming\Theme $theme
     * @param \Opis\JsonSchema\Validator $validator
     *
     * @throws \InvalidArgumentException
     */
    private function extractSchemaFromTemplate(string $template, Theme $theme, Validator $validator = null): array
    {
        $asset = $theme->asset($template);

        if (preg_match('#{% schema %}(.*){% endschema %}#s', $asset->value, $matches)) {
            $json = json_decode($matches[1]);
        } else {
            $json = [];
        }

        if ($validator) {
            $result = $validator->uriValidation(
                $json,
                'http://theming.givecloud.co/schemas/theme.json#'
            );

            if (! $result->isValid()) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid schema in [%s] detected at [%s].',
                    $template,
                    $result->getFirstError()->keyword()
                ));
            }
        }

        $schema = [];

        foreach ($json as $data) {
            if (isset($data->include)) {
                $schema = array_merge(
                    $schema,
                    $this->extractSchemaFromTemplate("snippets/{$data->include}.liquid", $theme, $validator)
                );
            } else {
                $schema[] = $data;
            }
        }

        return $schema;
    }

    /**
     * Set config based on schema.
     */
    private function setConfig()
    {
        $config = $this->templateConfig;

        foreach ($this->schema as $schema) {
            if (isset($schema->config)) {
                $config = array_merge($config, array_intersect_key(
                    (array) $schema->config,
                    $config
                ));
            }
        }

        $this->config = (object) $config;
    }

    /**
     * Retrieve private properties.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (in_array($key, ['suffix', 'config', 'schema'])) {
            return $this->{$key};
        }

        if ($key === 'slug') {
            return Str::slug($this->suffix);
        }

        if ($key === 'classes') {
            return "hide template-suffix template-suffix--{$this->slug}";
        }
    }
}
