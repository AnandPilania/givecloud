<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class FieldDrop extends Drop
{
    protected $attributes = [
        'id',
    ];

    protected function initialize($source)
    {
        $this->liquid = [
            'type' => $source->type,
            'label' => $source->name,
            'param_name' => "cf{$source->id}",
            'is_required' => $source->isrequired,
            'placeholder' => null,
            'hint' => $source->hint,
            'body' => do_shortcode($source->body),
            'choices' => $source->choices,
            'default_value' => $source->default_value,
        ];
    }

    public function options()
    {
        return ($this->source->type == 'select' || $this->source->type == 'multi-select') ? explode("\n", str_replace("\r\n", "\n", $this->source->options)) : null;
    }
}
