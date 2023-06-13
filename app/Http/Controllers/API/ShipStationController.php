<?php

namespace Ds\Http\Controllers\API;

use DomainException;
use Ds\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\ArrayToXml\ArrayToXml;

class ShipStationController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        // do nothing
    }

    /**
     * Handle ShipStation Custom Store requests.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function customStore(Request $request)
    {
        if (sys_get('shipstation_user') !== $request->getUser() || sys_get('shipstation_pass') !== $request->getPassword()) {
            return response('Authentication failed', 401)->header('WWW-Authenticate', 'Basic realm="Access denied"');
        }

        switch ($request->input('action')) {
            case 'export':     return $this->getOrders($request);
            case 'shipnotify': return $this->postShipment($request);
        }

        return response('Bad request', 400);
    }

    /**
     * Return Order information.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    private function getOrders(Request $request)
    {
        $orders = Order::query()
            ->where('shippable_items', '>', 0)
            ->where(function ($query) {
                $query->whereNotNull('shipping_last_name');
                $query->whereNotNull('shipaddress1');
                $query->whereNotNull('shipcity');
                $query->whereNotNull('shipzip');
                $query->whereNotNull('shipcountry');
            })->paid();

        if ($startDate = fromUtc($request->input('start_date'))) {
            $orders->where('updated_at', '>=', $startDate);
        }

        if ($endDate = fromUtc($request->input('end_date'))) {
            $orders->where('updated_at', '<=', $endDate);
        }

        $orders = $orders->paginate(250);

        $rootElement = [
            'rootElementName' => 'Orders',
            '_attributes' => [
                'pages' => $orders->lastPage(),
            ],
        ];

        if (count($orders)) {
            $orders = ['Order' => $orders->items()];

            foreach ($orders['Order'] as $key => $order) {
                try {
                    $orders['Order'][$key] = $this->conformOrder($order);
                } catch (DomainException $e) {
                    unset($orders['Order'][$key]);
                }
            }
        } else {
            $orders = [];
        }

        $xml = ArrayToXml::convert($orders, $rootElement, true, 'UTF-8');

        return response($xml)->header('Content-Type', 'text/xml');
    }

    /**
     * Conforms model to ShipStation schema.
     *
     * @param \Ds\Models\Order $order
     * @return array
     */
    private function conformOrder(Order $order): array
    {
        if ($order->refunded_amt > 0) {
            $status = 'cancelled';
        } elseif ($order->iscomplete) {
            $status = 'shipped';
        } elseif ($order->is_paid) {
            $status = 'paid';
        } else {
            $status = 'unpaid';
        }

        $conformedOrder = [
            'OrderID' => $this->cdata($order->id, 50),
            'OrderNumber' => $this->cdata($order->client_uuid, 50),
            'OrderDate' => fromUtcFormat($order->confirmationdatetime, 'm/d/Y H:i'),
            'OrderStatus' => $this->cdata($status, 50),
            'LastModified' => fromUtcFormat($order->updated_at, 'm/d/Y H:i'),
            'ShippingMethod' => $this->cdata($order->shipping_method_name, 100),
            'PaymentMethod' => $this->cdata($order->payment_type_formatted, 50),
            'OrderTotal' => $this->decimal($order->totalamount),
            'TaxAmount' => $this->decimal($order->taxtotal),
            'ShippingAmount' => $this->decimal($order->shipping_amount),
            'CustomerNotes' => $this->cdata($order->comments, 1000),
            'Source' => $this->cdata($order->source, 50),
            'Customer' => [
                'CustomerCode' => ($order->billingemail) ? $this->cdata($order->billingemail, 50) : $this->cdata($order->client_uuid, 50),
                'BillTo' => [
                    'Name' => $this->cdata($order->billing_display_name, 100),
                    'Company' => $this->cdata($order->billing_organization_name, 100),
                    'Phone' => $this->cdata($order->billingphone, 50),
                    'Email' => $this->cdata($order->billingemail, 100),
                ],
                'ShipTo' => [
                    'Name' => $this->cdata($order->shipping_display_name, 100),
                    'Company' => $this->cdata($order->shipping_organization_name, 100),
                    'Address1' => $this->cdata($order->shipaddress1, 200),
                    'Address2' => $this->cdata($order->shipaddress2, 200),
                    'City' => $this->cdata($order->shipcity, 100),
                    'State' => $this->cdata($order->shipstate, 100),
                    'PostalCode' => $this->cdata($order->shipzip, 50),
                    'Country' => $this->cdata($order->shipcountry, 2),
                    'Phone' => $this->cdata($order->shipphone, 50),
                ],
            ],
            'Items' => ['Item' => []],
        ];

        foreach ($order->items as $item) {
            $conformedItem = [
                'LineItemID' => $this->cdata($item->id, 50),
                'SKU' => $this->cdata($item->reference, 50),
                'Name' => $this->cdata($item->description, 200),
                'ImageUrl' => $this->cdata($item->image_thumb, 500),
                'Weight' => $this->decimal($item->variant->weight ?? 0),
                'WeightUnits' => $this->cdata('Pounds'),
                'Quantity' => $this->integer($item->qty, 1, 99999),
                'UnitPrice' => $this->decimal($item->price),
                'Adjustment' => $this->boolean(false),
            ];

            if (count($item->fields)) {
                $conformedItem['Options'] = ['Option' => []];
                foreach ($item->fields as $field) {
                    $conformedItem['Options']['Option'][] = [
                        'Name' => $this->cdata($field->name, 100),
                        'Value' => $this->cdata($field->value_formatted, 100),
                    ];
                }
            }

            $conformedOrder['Items']['Item'][] = $conformedItem;
        }

        $this->validateOrder($conformedOrder);

        return $conformedOrder;
    }

    /**
     * Wrap value with CDATA markers.
     *
     * @param string $string
     * @param int $limit
     * @return array|null
     */
    private function cdata($string, $limit = 100)
    {
        if ($string) {
            return ['_cdata' => Str::limit((string) $string, $limit, '')];
        }
    }

    /**
     * Format a DECIMAL field.
     *
     * @param mixed $number
     * @param int $precision
     * @return string
     */
    private function decimal($number, $precision = 2)
    {
        return number_format(round((float) $number, $precision), $precision, '.', '');
    }

    /**
     * Format an INTEGER field.
     *
     * @param mixed $number
     * @param int $min
     * @param int $max
     * @return int
     */
    private function integer($number, $min = 0, $max = 99999)
    {
        return max($min, min((int) $number, $max));
    }

    /**
     * Format an BOOLEAN field.
     *
     * @param mixed $condition
     * @return string
     */
    private function boolean($condition)
    {
        return $condition ? 'true' : 'false';
    }

    /**
     * Validate the order.
     *
     * @param array $order
     * @return bool
     */
    private function validateOrder(array $order)
    {
        $validator = app('validator')->make($order, [
            'OrderID' => 'required',
            'OrderNumber' => 'required',
            'OrderDate' => 'required',
            'OrderStatus' => 'required',
            'LastModified' => 'required',
            'OrderTotal' => 'required',
            'ShippingAmount' => 'required',
            'Customer.CustomerCode' => 'required',
            'Customer.BillTo.Name' => 'required',
            'Customer.ShipTo.Address1' => 'required',
            'Customer.ShipTo.City' => 'required',
            'Customer.ShipTo.State' => 'required',
            'Customer.ShipTo.PostalCode' => 'required',
            'Customer.ShipTo.Country' => 'required',
            'Items.Item.*.SKU' => 'required',
            'Items.Item.*.Name' => 'required',
            'Items.Item.*.Quantity' => 'required',
            'Items.Item.*.UnitPrice' => 'required',
        ]);

        if ($validator->fails()) {
            throw new DomainException($validator->errors()->first());
        }

        return true;
    }

    /**
     * Update Order with shipment information.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|string
     */
    private function postShipment(Request $request)
    {
        $order = Order::whereClientUuid($request->input('order_number'))->first();

        if ($order) {
            $order->iscomplete = true;
            $order->save();

            return 'Ok';
        }

        return response('Contribution not found', 404);
    }
}
