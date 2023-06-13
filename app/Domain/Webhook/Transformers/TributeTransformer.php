<?php

namespace Ds\Domain\Webhook\Transformers;

use Ds\Models\Tribute;
use League\Fractal\TransformerAbstract;

class TributeTransformer extends TransformerAbstract
{
    /** @var array */
    protected $defaultIncludes = [
        'tribute_type',
        'order_item',
    ];

    /**
     * @param \Ds\Models\Tribute $tribute
     * @return array
     */
    public function transform(Tribute $tribute)
    {
        return [
            'id' => (int) $tribute->id,
            'name' => $tribute->name,
            'amount' => number_format($tribute->amount, 2, '.', ''),
            'message' => $tribute->message,
            'notify' => $tribute->notify,
            'notify_contact' => [
                'name' => $tribute->name ?: null,
                'email' => $tribute->notify_email ?: null,
                'address1' => $tribute->notify_address ?: null,
                'address2' => null,
                'city' => $tribute->notify_city ?: null,
                'state' => $tribute->notify_state ?: null,
                'zip' => $tribute->notify_zip ?: null,
                'country' => $tribute->notify_country ?: null,
            ],
            'notify_at' => ($tribute->notify_at) ? toUtcFormat($tribute->notify_at, 'json') : null,
            'notified_at' => ($tribute->notified_at) ? toUtcFormat($tribute->notified_at, 'json') : null,
        ];
    }

    /**
     * @param \Ds\Models\Tribute $tribute
     * @return \League\Fractal\Resource\Item|void
     */
    public function includeTributeType(Tribute $tribute)
    {
        if ($tribute->tributeType) {
            return $this->item($tribute->tributeType, new TributeTypeTransformer);
        }
    }

    /**
     * @param \Ds\Models\Tribute $tribute
     * @return \League\Fractal\Resource\Item|void
     */
    public function includeOrderItem(Tribute $tribute)
    {
        if ($tribute->orderItem) {
            return $this->item($tribute->orderItem, new OrderItemTransformer);
        }
    }
}
