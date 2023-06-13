<?php

namespace Ds\Domain\Commerce\Shipping;

use Ds\Models\Order;

class ShipmentOptions
{
    public $transaction_id;
    public $from_name;
    public $from_company;
    public $from_state;
    public $from_zip;
    public $from_country;
    public $ship_name;
    public $ship_company;
    public $ship_phone;
    public $ship_address_1;
    public $ship_address_2;
    public $ship_city;
    public $ship_state;
    public $ship_zip;
    public $ship_country;
    public $weight_type;
    public $weight;
    public $contents_value;

    /**
     * Create an instance.
     *
     * @param \Ds\Models\Order $order
     */
    public function __construct(Order $order)
    {
        $this->transaction_id = $order->client_uuid;
        $this->from_name = sys_get('clientName');
        $this->from_company = '';
        $this->from_state = sys_get('shipping_from_state');
        $this->from_zip = sys_get('shipping_from_zip');
        $this->from_country = sys_get('shipping_from_country');
        $this->ship_name = (string) $order->shipname;
        $this->ship_company = '';
        $this->ship_phone = (string) $order->shipphone;
        $this->ship_address_1 = (string) $order->shipaddress1;
        $this->ship_address_2 = (string) $order->shipaddress2;
        $this->ship_city = (string) $order->shipcity;
        $this->ship_state = (string) $order->shipstate;
        $this->ship_zip = (string) $order->shipzip;
        $this->ship_country = (string) $order->shipcountry;
        $this->weight_type = 'LB';
        $this->weight = (float) $order->total_weight;
        $this->contents_value = 0;
    }
}
