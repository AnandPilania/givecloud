<?php

namespace Ds\Domain\Webhook\Transformers;

use Ds\Models\TributeType;
use League\Fractal\TransformerAbstract;

class TributeTypeTransformer extends TransformerAbstract
{
    /**
     * @param \Ds\Models\TributeType $tributeType
     * @return array
     */
    public function transform(TributeType $tributeType)
    {
        return [
            'id' => (int) $tributeType->id,
            'is_enabled' => (bool) $tributeType->is_enabled,
            'label' => $tributeType->label,
            'sequence' => $tributeType->sequence,
            'email_subject' => $tributeType->email_subject,
            'email_cc' => $tributeType->email_cc,
            'email_bcc' => $tributeType->email_bcc,
            'email_template' => $tributeType->email_template,
            'letter_template' => $tributeType->letter_template,
        ];
    }
}
