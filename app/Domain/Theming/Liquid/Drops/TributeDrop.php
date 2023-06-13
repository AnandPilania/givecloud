<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class TributeDrop extends Drop
{
    /** @var \Ds\Models\Tribute */
    protected $source;

    protected function initialize($source)
    {
        $this->liquid = [
            'type' => $source->tributeType->label ?? null,
            'name' => $source->name,
            'amount' => $source->amount,
            'message' => $source->message,
            'recipient_name' => $source->notify_name,
            'recipient_email' => $source->notify_email,
            'recipient_address' => $source->notify_address,
            'recipient_city' => $source->notify_city,
            'recipient_state' => $source->notify_state,
            'recipient_zip' => $source->notify_zip,
            'recipient_country' => $source->notify_country,
            'notification_type' => $source->notify,
            'notify_at' => $source->notify_at,
        ];
    }

    public function recipient_address_html()
    {
        return nl2br(address_format(
            $this->liquid['recipient_address'],
            null,
            $this->liquid['recipient_city'],
            $this->liquid['recipient_state'],
            $this->liquid['recipient_zip'],
            $this->liquid['recipient_country']
        ));
    }
}
