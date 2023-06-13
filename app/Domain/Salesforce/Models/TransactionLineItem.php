<?php

namespace Ds\Domain\Salesforce\Models;

class TransactionLineItem extends LineItem
{
    public function getCompoundKey()
    {
        return $this->model->id . '-' . $this->model->recurringPaymentProfile->order_item->id;
    }

    public function fields(): array
    {
        $lineItem = $this->model->recurringPaymentProfile->order_item;

        return [
            'Givecloud__Givecloud_Line_Item_Identifier__c' => $this->getCompoundKey(),

            'Givecloud__Related_Line_Item__r' => [
                'Givecloud__Givecloud_Line_Item_Identifier__c' => $lineItem->id,
            ],
            'Givecloud__Contribution__r' => [
                'Givecloud__Givecloud_Contribution_Identifier__c' => (new Transaction)->forModel($this->model)->getCompoundKey(),
            ],
            'Name' => $this->name($lineItem),
            'Givecloud__Total__c' => $this->model->amt,
            'Givecloud__Image_Thumbnail__c' => $lineItem->image_thumb,
            'Givecloud__Description__c' => $lineItem->description,
            'Givecloud__Locked__c' => (bool) $lineItem->is_locked,
            'Givecloud__Recurring__c' => (bool) $lineItem->is_recurring,
            'Givecloud__Price_Reduced__c' => (bool) $lineItem->is_price_reduced,
            'Givecloud__Undiscounted_Price__c' => $lineItem->undiscounted_price,
            'Givecloud__Locked_Original_Price__c' => $lineItem->locked_original_price,
            'Givecloud__Locked_Variants_Original_Price__c' => $lineItem->locked_variants_original_price,
            'Givecloud__Locked_Variants_Price__c' => $lineItem->locked_variants_price,
            'Givecloud__Locked_Variants_Total__c' => $lineItem->locked_variants_total,
            'Givecloud__Payment_String__c' => $lineItem->payment_string,
            'Givecloud__Public_URL__c' => $lineItem->public_url,
            'Givecloud__Recurring_Description__c' => $lineItem->recurring_description,
            'Givecloud__Reference__c' => $lineItem->reference,
            'Givecloud__DPO_Tribute_ID__c' => $lineItem->dpo_tribute_id,
            'Givecloud__Gift_Aid__c' => (bool) $lineItem->gift_aid,
            'Givecloud__Tribute__c' => (bool) $lineItem->is_tribute,
            'Givecloud__Price__c' => $lineItem->price,
            'Givecloud__Quantity__c' => $lineItem->qty,
            'Givecloud__Sponsorship_Expired__c' => (bool) $lineItem->sponsorship_is_expired,
            'Givecloud__Total_Tax_Amount__c' => $lineItem->total_tax_amt,
            'Givecloud__Recurring_Amount__c' => null,
            'Givecloud__Recurring_Day__c' => null,
            'Givecloud__Recurring_Day_of_Week__c' => null,
            'Givecloud__Recurring_with_DPO__c' => false,
            'Givecloud__Recurring_with_Initial_Charge__c' => false,
            'Givecloud__Recurring_Cycles__c' => null,
            'Givecloud__Recurring_Starts_On__c' => null,
            'Givecloud__Recurring_Ends_On__c' => null,
        ];
    }

    public function savesExternalReferenceLocally(): bool
    {
        return false;
    }
}
