<?php

namespace Ds\Services\Order;

class OrderAddItemRequest
{
    /** @var int */
    public $variant_id;

    /** @var float */
    public $amt;

    /** @var int */
    public $qty;

    /** @var string */
    public $recurring_frequency;

    /** @var int */
    public $recurring_day;

    /** @var int */
    public $recurring_day_of_week;

    /** @var bool */
    public $recurring_with_initial_charge;

    /** @var bool */
    public $recurring_with_dpo;

    /** @var bool */
    public $is_tribute;

    /** @var int */
    public $dpo_tribute_id;

    /** @var int */
    public $tribute_type_id;

    /** @var string */
    public $tribute_name;

    /** @var string */
    public $tribute_message;

    /** @var string */
    public $tribute_notify;

    /** @var string */
    public $tribute_notify_name;

    public $tribute_notify_at;

    /** @var string */
    public $tribute_notify_email;

    /** @var string */
    public $tribute_notify_address;

    /** @var string */
    public $tribute_notify_city;

    /** @var string */
    public $tribute_notify_state;

    /** @var string */
    public $tribute_notify_zip;

    /** @var string */
    public $tribute_notify_country;

    /** @var array */
    public $fields;

    /** @var array */
    public $gl_code;

    /** @var string */
    public $public_message;

    /** @var int */
    public $fundraising_page_id;

    /** @var int */
    public $fundraising_member_id;

    /** @var bool */
    public $gift_aid;

    /** @var array */
    public $metadata;

    public function __construct(array $data)
    {
        $this->variant_id = $data['variant_id'];
        $this->amt = $data['amt'];
        $this->qty = $data['qty'];
        $this->recurring_frequency = $data['recurring_frequency'];
        $this->recurring_day = $data['recurring_day'];
        $this->recurring_day_of_week = $data['recurring_day_of_week'];
        $this->recurring_with_initial_charge = $data['recurring_with_initial_charge'];
        $this->recurring_with_dpo = $data['recurring_with_dpo'];
        $this->is_tribute = $data['is_tribute'];
        $this->dpo_tribute_id = $data['dpo_tribute_id'];
        $this->tribute_type_id = $data['tribute_type_id'];
        $this->tribute_name = $data['tribute_name'];
        $this->tribute_message = $data['tribute_message'];
        $this->tribute_notify = $data['tribute_notify'];
        $this->tribute_notify_name = $data['tribute_notify_name'];
        $this->tribute_notify_at = $data['tribute_notify_at'];
        $this->tribute_notify_email = $data['tribute_notify_email'];
        $this->tribute_notify_address = $data['tribute_notify_address'];
        $this->tribute_notify_city = $data['tribute_notify_city'];
        $this->tribute_notify_state = $data['tribute_notify_state'];
        $this->tribute_notify_zip = $data['tribute_notify_zip'];
        $this->tribute_notify_country = $data['tribute_notify_country'];
        $this->fields = $data['fields'];
        $this->gl_code = $data['gl_code'];
        $this->public_message = $data['public_message'];
        $this->fundraising_page_id = $data['fundraising_page_id'];
        $this->fundraising_member_id = $data['fundraising_member_id'];
        $this->gift_aid = $data['gift_aid'];
        $this->metadata = $data['metadata'];
    }
}
