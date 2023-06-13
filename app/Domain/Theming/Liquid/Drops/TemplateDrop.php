<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Support\Str;

class TemplateDrop extends Drop
{
    /**
     * Create an instance.
     *
     * @param string $templatePath
     */
    public function __construct(string $templatePath)
    {
        $this->templatePath = $templatePath;

        $templatePath = preg_replace('/^templates\//', '', $templatePath);
        $templatePath = preg_replace('/\.liquid$/', '', $templatePath);

        $this->liquid = [
            'name' => basename($templatePath),
            'type' => Str::before($templatePath, '.'),
            'suffix' => null,
            'directory' => Str::contains($templatePath, '/') ? dirname($templatePath) : null,
        ];

        if (Str::contains($this->liquid['name'], '.')) {
            $this->liquid['suffix'] = Str::after($this->liquid['name'], '.');
        }
    }

    public function class_name()
    {
        $klass = preg_replace('/-liquid$/', '', $this->templatePath);
        $klass = preg_replace('/^templates\//', 'template/', $klass);

        $klass = str_replace('.', '-', $klass);

        return str_replace('/', '--', $klass);
    }

    /**
     * Output as string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->liquid['name'];
    }
}
