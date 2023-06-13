<?php

namespace Ds\Services\Order;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Variant;
use Illuminate\Contracts\Validation\Factory;

class OrderAddItemRequestValidationService
{
    /** @var \Illuminate\Contracts\Validation\Factory */
    protected $validator;

    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    public function validate(array $data): OrderAddItemRequest
    {
        $data = $this->getDataDefaults($data);

        $validator = $this->validator->make($data, $this->getValidationRules($data), $this->getValidationMessages());

        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }

        return new OrderAddItemRequest($data);
    }

    protected function getDataDefaults(array $data): array
    {
        return [
            'variant_id' => $data['variant_id'] ?? null,
            'amt' => $data['amt'] ?? 0.00,
            'qty' => $data['qty'] ?? 1,
            'recurring_frequency' => $data['recurring_frequency'] ?? null,
            'recurring_day' => $data['recurring_day'] ?? null,
            'recurring_day_of_week' => $data['recurring_day_of_week'] ?? null,
            'recurring_with_initial_charge' => $data['recurring_with_initial_charge'] ?? false,
            'recurring_with_dpo' => $data['recurring_with_dpo'] ?? (bool) sys_get('rpp_donorperfect'),
            'is_tribute' => $data['is_tribute'] ?? false,
            'dpo_tribute_id' => $data['dpo_tribute_id'] ?? null,
            'tribute_type_id' => $data['tribute_type_id'] ?? null,
            'tribute_name' => $data['tribute_name'] ?? null,
            'tribute_message' => $data['tribute_message'] ?? null,
            'tribute_notify' => $data['tribute_notify'] ?? null,
            'tribute_notify_name' => $data['tribute_notify_name'] ?? null,
            'tribute_notify_at' => $data['tribute_notify_at'] ?? null,
            'tribute_notify_email' => $data['tribute_notify_email'] ?? null,
            'tribute_notify_address' => $data['tribute_notify_address'] ?? null,
            'tribute_notify_city' => $data['tribute_notify_city'] ?? null,
            'tribute_notify_state' => $data['tribute_notify_state'] ?? null,
            'tribute_notify_zip' => $data['tribute_notify_zip'] ?? null,
            'tribute_notify_country' => $data['tribute_notify_country'] ?? null,
            'fields' => $data['fields'] ?? [],
            'gl_code' => $data['gl_code'] ?? null,
            'public_message' => $data['public_message'] ?? null,
            'fundraising_page_id' => $data['fundraising_page_id'] ?? null,
            'fundraising_member_id' => $data['fundraising_member_id'] ?? null,
            'gift_aid' => $data['gift_aid'] ?? false,
            'metadata' => $data['metadata'] ?? [],
        ];
    }

    protected function getValidationRules(array $data): array
    {
        $variant = Variant::find($data['variant_id']);
        $rppDefaultType = $variant->product->recurring_type ?? sys_get('rpp_default_type');

        return [
            'variant_id' => 'required|integer|exists:productinventory,id,is_deleted,0',
            'amt' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:1',
            'recurring_frequency' => 'nullable|in:weekly,biweekly,monthly,quarterly,biannually,annually',
            'recurring_day' => [
                $rppDefaultType === 'fixed'
                    ? 'required_if:recurring_frequency,monthly,quarterly,biannually,annually'
                    : 'nullable',
                'integer',
                'min:1',
                'max:31',
            ],
            'recurring_day_of_week' => [
                $rppDefaultType === 'fixed'
                    ? 'required_if:recurring_frequency,weekly,biweekly'
                    : 'nullable',
                'integer',
                'min:1',
                'max:7',
            ],
            'recurring_with_initial_charge' => 'nullable|boolean',
            'recurring_with_dpo' => 'nullable|boolean',
            'is_tribute' => 'nullable|boolean',
            'dpo_tribute_id' => 'nullable|integer',
            'tribute_type_id' => 'required_if:is_tribute,1|integer|min:1',
            'tribute_name' => 'required_if:is_tribute,1',
            'tribute_message' => 'nullable',
            'tribute_notify' => 'nullable|in:email,letter',
            'tribute_notify_name' => 'required_with:tribute_notify',
            'tribute_notify_at' => 'nullable',
            'tribute_notify_email' => 'required_if:tribute_notify,email|email',
            'tribute_notify_address' => 'required_if:tribute_notify,letter',
            'tribute_notify_city' => 'required_if:tribute_notify,letter',
            'tribute_notify_state' => 'required_if:tribute_notify,letter',
            'tribute_notify_zip' => 'required_if:tribute_notify,letter',
            'tribute_notify_country' => 'required_if:tribute_notify,letter',
            'fields' => 'nullable|array',
            'public_message' => 'nullable',
            'fundraising_page_id' => 'nullable|integer|exists:fundraising_pages,id',
            'fundraising_member_id' => 'nullable|integer|exists:member,id',
            'gift_aid' => 'nullable|boolean',
            'metadata' => 'nullable|array',
        ];
    }

    protected function getValidationMessages(): array
    {
        return [
            'variant_id.required' => 'No product selected.',
            'variant.exists' => 'Error retrieving product information.',
            'qty.min' => 'Quantity is 0.',
        ];
    }
}
