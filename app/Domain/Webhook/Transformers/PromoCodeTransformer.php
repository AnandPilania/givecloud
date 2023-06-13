<?php

namespace Ds\Domain\Webhook\Transformers;

use Ds\Models\PromoCode;
use League\Fractal\TransformerAbstract;

class PromoCodeTransformer extends TransformerAbstract
{
    /**
     * @param \Ds\Models\PromoCode $promoCode
     * @return array
     */
    public function transform(PromoCode $promoCode)
    {
        $data = [
            'id' => (int) $promoCode->id,
            'code' => $promoCode->code,
            'description' => $promoCode->description ?: null,
            'discount' => (float) $promoCode->discount,
            'discount_type' => $promoCode->discount_type,
            'start_date' => toUtcFormat($promoCode->startdate, 'Y-m-d'),
            'end_date' => toUtcFormat($promoCode->enddate, 'Y-m-d'),
            'free_shipping' => (bool) $promoCode->is_free_shipping,
            'free_shipping_description' => $promoCode->free_shipping_label ?: null,
            'created_at' => toUtcFormat($promoCode->createddatetime, 'json'),
            'updated_at' => toUtcFormat($promoCode->modifieddatetime, 'json'),
        ];

        if ($promoCode->discount_type === 'dollar') {
            $data['discount'] = number_format($promoCode->discount, 2, '.', '');
            $data['discount_type'] = 'amount';
        }

        return $data;
    }
}
