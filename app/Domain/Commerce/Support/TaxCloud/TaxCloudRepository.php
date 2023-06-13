<?php

namespace Ds\Domain\Commerce\Support\TaxCloud;

use DomainException;
use Ds\Models\Order;
use Throwable;

class TaxCloudRepository
{
    /**
     * Get tax rates from TaxCloud for each item in the order.
     *
     * @param \Ds\Models\Order $order
     * @return void
     */
    public function applyTaxes(Order $order)
    {
        // if there are missing parameters that are
        // preventing us from asking TaxCloud for
        // tax rates, don't bother communicating
        // with TaxCloud and just set the taxtotal
        // to $0.00
        if (! $this->_canApplyTax($order)) {
            // set cart tax to 0.00
            $order->taxtotal = 0.00;

        // if we have all the info we need to ask
        // TaxCloud for rates...
        } else {
            if ($order->is_pos) {
                $destination = [
                    'Address1' => $order->shipaddress1,
                    'Address2' => $order->shipaddress2,
                    'City' => $order->shipcity,
                    'State' => $order->shipstate,
                    'Zip5' => $this->_zip5($order->shipzip),
                    'Zip4' => $this->_zip4($order->shipzip),
                ];
            } elseif ($order->shippable_items > 0) {
                $destination = [
                    'Address1' => $order->shipaddress1,
                    'Address2' => $order->shipaddress2,
                    'City' => $order->shipcity,
                    'State' => $order->shipstate,
                    'Zip5' => $this->_zip5($order->shipzip),
                    'Zip4' => $this->_zip4($order->shipzip),
                ];
            } else {
                $destination = [
                    'Address1' => $order->billingaddress1,
                    'Address2' => $order->billingaddress2,
                    'City' => $order->billingcity,
                    'State' => $order->billingstate,
                    'Zip5' => $this->_zip5($order->billingzip),
                    'Zip4' => $this->_zip4($order->billingzip),
                ];
            }

            // build payload required by tax cloud
            // which contains info about the order
            // and all the items in the order
            $payload = [
                'deliveredBySeller' => false,
                'cartID' => $order->client_uuid,
                'customerID' => sha1($order->billingemail ?: 'anonymous'),
                'origin' => [
                    'Address1' => sys_get('taxcloud_origin_address1'),
                    'Address2' => sys_get('taxcloud_origin_address2'),
                    'City' => sys_get('taxcloud_origin_city'),
                    'State' => sys_get('taxcloud_origin_state'),
                    'Zip5' => $this->_zip5(sys_get('taxcloud_origin_zip')),
                    'Zip4' => $this->_zip4(sys_get('taxcloud_origin_zip')),
                ],
                'destination' => $destination,
                'cartItems' => [],
            ];

            // loop over each item in the cart and populate the item_data
            // required to get tax rates
            $order->items->each(function ($item, $key) use (&$payload) {
                // make sure the product has a tax id before adding it
                if ($item->variant->product->taxcloud_tic_id) {
                    $payload['cartItems'][] = [
                        'Qty' => $item->qty,
                        'Price' => $item->price,
                        'TIC' => $item->variant->product->taxcloud_tic_id,
                        'ItemID' => $item->code,
                        'Index' => count($payload['cartItems']),
                    ];
                }
            });

            // if there is shipping, send shipping as a line-item
            if ($order->shipping_amount > 0) {
                // if courier, 11010; otherwise 11000
                $shipping_taxcloud_tic_id = ($order->courier_method) ? '11010' : '11000';

                // add dummy line-item representing shipping
                $payload['cartItems'][] = [
                    'Qty' => 1,
                    'Price' => $order->shipping_amount,
                    'TIC' => $shipping_taxcloud_tic_id,
                    'ItemID' => 'SHIPPING',
                    'Index' => count($payload['cartItems']),
                ];
            }

            // if no items to send to taxcloud, bail and set taxtotal = 0
            // !! IMPROVE THIS !!
            // It should be moved into the _canApplyTax
            // validation function (ensure num of products with
            // taxcloud_tic_id is > 0)
            if (count($payload['cartItems']) == 0) {
                $order->taxtotal = 0;

                return;
            }

            // get the rates from TaxCloud
            // if taxcloud fails, fail gracefully while also notifying bugsnag
            try {
                $item_rates = app('taxCloud')->lookup($payload);
            } catch (DomainException $e) {
                $order->taxtotal = 0;

                return;
            } catch (Throwable $e) {
                notifyException($e);
                $order->taxtotal = 0;

                return;
            }

            // add up total tax off all items returned from taxcloud
            $total_tax = 0;

            // aggregate the total tax for the cart
            collect($item_rates)->each(function ($item, $key) use (&$total_tax) {
                $total_tax += (float) $item->TaxAmount;
            });

            // apply totaltax to order
            $order->taxtotal = $total_tax;
        }
    }

    /**
     * Send charged taxes to tax cloud after an order has been processed.
     *
     * @param \Ds\Models\Order $order
     * @return void
     */
    public function capture(Order $order)
    {
        if (! $order->taxtotal) {
            return;
        }

        $payload = [
            'cartID' => $order->client_uuid,
            'orderID' => $order->client_uuid,
            'customerID' => sha1($order->billingemail ?: 'anonymous'),
            'dateAuthorized' => fromUtcFormat($order->confirmationdatetime, 'Y-m-d'),
            'dateCaptured' => fromUtcFormat($order->confirmationdatetime, 'Y-m-d'),
        ];

        app('taxCloud')->authorizedWithCapture($payload);
    }

    /**
     * Split ZIP, and return the first 5 only
     *
     * @param string $zip
     * @return string
     */
    private function _zip5($zip = '')
    {
        $z = explode('-', trim($zip));
        if (count($z) > 0) {
            return trim($z[0]);
        }

        return trim($z);
    }

    /**
     * Split ZIP, and return the last 4 only
     *
     * @param string $zip
     * @return string
     */
    private function _zip4($zip = '')
    {
        $z = explode('-', trim($zip));
        if (count($z) > 1) {
            return trim($z[1]);
        }

        return '';
    }

    /**
     * Validate the order to see if tax can be applied.
     * Do we know enough about the order to apply tax?
     *
     * @param \Ds\Models\Order $order
     * @return bool
     */
    public function _canApplyTax(Order $order)
    {
        // check params that are the same between all types of orders
        // - must have items in the order (otherwise no taxable amount)
        // - origin address must exist
        if ($order->items->count() === 0
            || sys_get('taxcloud_origin_address1') == ''
            || sys_get('taxcloud_origin_city') == ''
            || sys_get('taxcloud_origin_state') == ''
            || sys_get('taxcloud_origin_zip') == '') {
            return false;
        }

        if ($order->is_pos) {
            return ! ($order->tax_address1 == ''
                || $order->tax_city == ''
                || $order->tax_state == ''
                || $order->tax_zip == ''
                || $order->tax_country != 'US');
        }

        if ($order->shippable_items > 0) {
            return ! ($order->shipaddress1 == ''
                || $order->shipcity == ''
                || $order->shipstate == ''
                || $order->shipzip == ''
                || $order->shipcountry != 'US');
        }

        return ! ($order->billingaddress1 == ''
                || $order->billingcity == ''
                || $order->billingstate == ''
                || $order->billingzip == ''
                || $order->billingcountry != 'US');
    }
}
