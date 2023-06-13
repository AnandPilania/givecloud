<?php

namespace Ds\Domain\Webhook\Transformers;

use Ds\Models\ProductCustomField;
use League\Fractal\TransformerAbstract;

class ProductCustomFieldTransformer extends TransformerAbstract
{
    /**
     * @param \Ds\Models\ProductCustomField $field
     * @return array
     */
    public function transform(ProductCustomField $field)
    {
        $data = [
            'id' => (int) $field->id,
            'sequence' => (int) $field->sequence ?: null,
            'type' => $field->type,
            'name' => $field->name,
            'required' => (bool) $field->isrequired,
            'body' => $field->body ?: null,
            'options' => array_filter(array_map('trim', explode("\n", $field->options)), 'strlen'),
            'hint' => $field->hint ?: null,
        ];

        if ($field->pivot) {
            $data['value'] = $field->value_formatted;
        }

        return $data;
    }
}
