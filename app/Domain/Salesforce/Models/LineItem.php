<?php

namespace Ds\Domain\Salesforce\Models;

use Ds\Models\OrderItem;

class LineItem extends Model
{
    protected $table = 'Givecloud__Line_Item__c';

    public $externalKey = 'Givecloud__Givecloud_Line_Item_Identifier__c';

    public $columns = [
        'Id',
        'Name',
        'Givecloud__Givecloud_Line_Item_Identifier__c',
    ];

    public function getCompoundKey()
    {
        return (string) parent::getCompoundKey();
    }

    public function fields(): array
    {
        return [
            'Givecloud__Givecloud_Line_Item_Identifier__c' => $this->model->id,
            'Name' => $this->name($this->model),
            'Givecloud__Total__c' => $this->model->total,
            'Givecloud__Contribution__r' => [
                'Givecloud__Givecloud_Contribution_Identifier__c' => (new Contribution)->forModel($this->model->order)->getCompoundKey(),
            ],
            'Givecloud__Image_Thumbnail__c' => $this->model->image_thumb,
            'Givecloud__Description__c' => $this->model->description,
            'Givecloud__Locked__c' => (bool) $this->model->is_locked,
            'Givecloud__Recurring__c' => (bool) $this->model->is_recurring,
            'Givecloud__Price_Reduced__c' => (bool) $this->model->is_price_reduced,
            'Givecloud__Undiscounted_Price__c' => $this->model->undiscounted_price,
            'Givecloud__Locked_Original_Price__c' => $this->model->locked_original_price,
            'Givecloud__Locked_Variants_Original_Price__c' => $this->model->locked_variants_original_price,
            'Givecloud__Locked_Variants_Price__c' => $this->model->locked_variants_price,
            'Givecloud__Locked_Variants_Total__c' => $this->model->locked_variants_total,
            'Givecloud__Payment_String__c' => $this->model->payment_string,
            'Givecloud__Public_URL__c' => $this->model->public_url,
            'Givecloud__Recurring_Description__c' => $this->model->recurring_description,
            'Givecloud__Reference__c' => $this->model->reference,
            'Givecloud__DPO_Tribute_ID__c' => $this->model->dpo_tribute_id,
            'Givecloud__Gift_Aid__c' => (bool) $this->model->gift_aid,
            'Givecloud__Tribute__c' => (bool) $this->model->is_tribute,
            'Givecloud__Price__c' => $this->model->price,
            'Givecloud__Quantity__c' => $this->model->qty,
            'Givecloud__Recurring_Amount__c' => $this->model->recurring_amount,
            'Givecloud__Recurring_Day__c' => $this->model->recurring_day,
            'Givecloud__Recurring_Day_of_Week__c' => $this->model->recurring_day_of_week,
            'Givecloud__Recurring_with_DPO__c' => (bool) $this->model->recurring_with_dpo,
            'Givecloud__Recurring_with_Initial_Charge__c' => (bool) $this->model->recurring_with_initial_charge,
            'Givecloud__Recurring_Cycles__c' => $this->model->recurring_cycles,
            'Givecloud__Recurring_Starts_On__c' => $this->model->recurring_starts_on,
            'Givecloud__Recurring_Ends_On__c' => $this->model->recurring_ends_on,
            'Givecloud__Sponsorship_Expired__c' => (bool) $this->model->sponsorship_is_expired,
            'Givecloud__Total_Tax_Amount__c' => $this->model->total_tax_amt,
        ];
    }

    protected function name(OrderItem $orderItem): ?string
    {
        if ($orderItem->sponsorship) {
            return 'Sponsorship - ' . $orderItem->sponsorship->display_name;
        }

        if ($membership = data_get($orderItem, 'variant.membership')) {
            return $this->variantName($orderItem) . ' (' . $membership->name . ')';
        }

        if ($orderItem->fundraisingPage) {
            return $this->variantName($orderItem) . ' (' . $orderItem->fundraisingPage->title . ')';
        }

        if ($orderItem->variant) {
            return $this->variantName($orderItem);
        }

        return $orderItem->description;
    }

    protected function variantName(OrderItem $orderItem): ?string
    {
        return $orderItem->variant->product->name . ($orderItem->variant->variantname ? ' - ' . $orderItem->variant->variantname : '');
    }
}
