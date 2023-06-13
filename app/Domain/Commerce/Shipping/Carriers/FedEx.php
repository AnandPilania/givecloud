<?php

namespace Ds\Domain\Commerce\Shipping\Carriers;

use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Shipping\AbstractCarrier;
use Ds\Domain\Commerce\Shipping\Rate;
use Ds\Domain\Commerce\Shipping\ShipmentOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use SoapClient;
use SoapFault;
use Throwable;

class FedEx extends AbstractCarrier
{
    const ENDPOINT_TESTING = 'https://wsbeta.fedex.com:443/web-services/rate';
    const ENDPOINT_PRODUCTION = 'https://ws.fedex.com:443/web-services/rate';

    /** @var string */
    private $key;

    /** @var string */
    private $password;

    /** @var string */
    private $accountNumber;

    /** @var string */
    private $meterNumber;

    /** @var string */
    private $netDiscount;

    /** @var array */
    private $serviceCodes;

    /** @var bool */
    private $liveMode;

    /**
     * Create an instance.
     *
     * @param string $key
     * @param string $password
     * @param string $accountNumber
     * @param string $meterNumber
     * @param string $netDiscount
     * @param array $serviceCodes
     * @param bool $liveMode
     */
    public function __construct($key, $password, $accountNumber, $meterNumber, $netDiscount, array $serviceCodes = [], $liveMode = true)
    {
        $this->key = $key;
        $this->password = $password;
        $this->accountNumber = $accountNumber;
        $this->meterNumber = $meterNumber;
        $this->netDiscount = $netDiscount;
        $this->serviceCodes = $serviceCodes;
        $this->liveMode = (bool) $liveMode;
    }

    /**
     * Get the carrier handle.
     *
     * @return string
     */
    public function getHandle(): string
    {
        return 'fedex';
    }

    /**
     * Get the carrier name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'FedEx';
    }

    /**
     * Get the API endpoint.
     *
     * @return string
     */
    private function getEndpoint(): string
    {
        return $this->liveMode ? static::ENDPOINT_PRODUCTION : static::ENDPOINT_TESTING;
    }

    /**
     * Get shipping rates.
     *
     * @param \Ds\Domain\Commerce\Shipping\ShipmentOptions $options
     * @return \Illuminate\Support\Collection
     */
    public function getRates(ShipmentOptions $options): Collection
    {
        $rates = collect();

        $transaction_id = $options->transaction_id;
        $from_name = $options->from_name;
        $from_company = $options->from_company;
        $from_state = $options->from_state;
        $from_zip = $options->from_zip;
        $from_country = $options->from_country;
        $ship_name = $options->ship_name;
        $ship_company = $options->ship_company;
        $ship_phone = $options->ship_phone;
        $ship_address_1 = $options->ship_address_1;
        $ship_address_2 = $options->ship_address_2;
        $ship_city = $options->ship_city;
        $ship_zip = $options->ship_zip;
        $ship_country = $options->ship_country;
        $weight = max(0.1, round($options->weight, 2));
        $weight_type = $options->weight_type;

        if (empty($from_state) || empty($from_zip) || empty($from_country) || empty($ship_address_1) || empty($ship_city) || empty($ship_zip) || empty($ship_country)) {
            return $rates;
        }

        $data = [
            'ReturnTransitAndCommit' => true,
            'WebAuthenticationDetail' => [
                'UserCredential' => [
                    'Key' => $this->key,
                    'Password' => $this->password,
                ],
            ],
            'ClientDetail' => [
                'AccountNumber' => $this->accountNumber,
                'MeterNumber' => $this->meterNumber,
            ],
            'TransactionDetail' => [
                'CustomerTransactionId' => $transaction_id,
            ],
            'Version' => [
                'ServiceId' => 'crs',
                'Major' => '24',
                'Intermediate' => '0',
                'Minor' => '0',
            ],
            'RequestedShipment' => [
                'ShipTimestamp' => date('c'),
                'DropoffType' => 'REGULAR_PICKUP',
                // 'ServiceType' => 'INTERNATIONAL_PRIORITY',
                // 'PackagingType' => $packaging_type,
                'TotalInsuredValue' => [
                    'Ammount' => 100,
                    'Currency' => 'USD',
                ],
                'Shipper' => [
                    'Contact' => [
                        'PersonName' => $from_name,
                        'CompanyName' => $from_company,
                    ],
                    'Address' => [
                        'StateOrProvinceCode' => $from_state,
                        'PostalCode' => $from_zip,
                        'CountryCode' => $from_country,
                    ],
                ],
                'Recipient' => [
                    'Contact' => [
                        'PersonName' => $ship_name,
                        'CompanyName' => $ship_company,
                        'PhoneNumber' => $ship_phone,
                    ],
                    'Address' => [
                        'StreetLines' => [$ship_address_1, $ship_address_2],
                        'City' => $ship_city,
                        // 'StateOrProvinceCode' => $ship_state,
                        'PostalCode' => $ship_zip,
                        'CountryCode' => $ship_country,
                        'Residential' => true,
                    ],
                ],
                'ShippingChargesPayment' => [
                    'PaymentType' => 'SENDER',
                    'Payor' => [
                        'AccountNumber' => $this->accountNumber,
                        'CountryCode' => $from_country,
                    ],
                ],
                'RateRequestTypes' => 'ACCOUNT',
                'RateRequestTypes' => 'LIST',
                'PackageCount' => '1',
                'RequestedPackageLineItems' => [[
                    'SequenceNumber' => 1,
                    'GroupPackageCount' => 1,
                    'Weight' => [
                        'Value' => $weight,
                        'Units' => $weight_type,
                    ],
                    /*
                    'Dimensions' => [
                        'Length' => 1,
                        'Width' => 1,
                        'Height' => 1,
                        'Units' => 'IN',
                    ]
                    */
                ]],
            ],
        ];

        ini_set('default_socket_timeout', 5);

        $client = new SoapClient(__DIR__ . '/FedExRateService_v24.wsdl', [
            'connection_timeout' => 1,
            'exceptions' => true,
            'trace' => 1,
        ]);

        $client->__setLocation($this->getEndpoint());

        try {
            $res = $client->getRates($data);
        } catch (SoapFault $e) {
            notifyError('ShippingError', $e->getMessage(), function ($report) use ($data, $e) {
                $report->setMetaData([
                    'soap_request' => $data,
                    'soap_fault' => [
                        'faultcode' => $e->faultcode ?? null,
                        'faultstring' => $e->faultstring ?? null,
                        'faultactor' => $e->faultactor ?? null,
                        'detail' => $e->detail ?? null,
                        '_name' => $e->_name ?? null,
                        'headerfault' => $e->headerfault ?? null,
                    ],
                ]);
            });

            return $rates;
        } catch (Throwable $e) {
            notifyError('ShippingError', $e->getMessage(), function ($report) use ($data) {
                $report->setMetaData([
                    'soap_request' => $data,
                ]);
            });

            return $rates;
        }

        if ($res->HighestSeverity == 'FAILURE' || $res->HighestSeverity == 'ERROR') {
            notifyError('ShippingError', $res->Notifications->Message ?? 'Unknown Error', function ($report) use ($data, $res) {
                $report->setMetaData([
                    'soap_request' => $data,
                    'soap_response' => $res,
                ]);
            });
        } elseif (isset($res->RateReplyDetails)) {
            foreach ($res->RateReplyDetails as $rate) {
                if (! is_object($rate)) {
                    continue;
                }

                $code = (string) $rate->ServiceType;

                if ($this->serviceCodes && ! in_array($code, $this->serviceCodes, true)) {
                    continue;
                }

                $amount = (float) $rate->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;

                if ($this->netDiscount && isset($rate->RatedShipmentDetails[0]->EffectiveNetDiscount->Amount)) {
                    $amount += (float) $rate->RatedShipmentDetails[0]->EffectiveNetDiscount->Amount;
                }

                $rates[] = new Rate(
                    $this,
                    $code,
                    Arr::get(static::getServices(), $code, $code),
                    new Money(
                        $amount,
                        $rate->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Currency ?? 'USD'
                    )
                );
            }
        }

        return $rates;
    }

    /**
     * Get list of services.
     *
     * @return array
     */
    public static function getServices()
    {
        return [
            'SMART_POST' => 'FedEx Smart Post',
            'PRIORITY_OVERNIGHT' => 'FedEx Priority Overnight',
            'STANDARD_OVERNIGHT' => 'FedEx Standard Overnight',
            'FIRST_OVERNIGHT' => 'FedEx First Overnight',
            'FEDEX_2_DAY' => 'FedEx 2 Day',
            'FEDEX_2_DAY_AM' => 'FedEx 2 Day AM',
            'FEDEX_EXPRESS_SAVER' => 'FedEx Express Saver',
            'INTERNATIONAL_PRIORITY' => 'FedEx International Priority',
            'INTERNATIONAL_ECONOMY' => 'FedEx International Economy',
            'INTERNATIONAL_FIRST' => 'FedEx International First',
            'FEDEX_FIRST_FREIGHT' => 'FedEx First Freight',
            'FEDEX_FREIGHT_ECONOMY' => 'FedEx Freight Economy',
            'FEDEX_FREIGHT_PRIORITY' => 'FedEx Freight Priority',
            'FEDEX_1_DAY_FREIGHT' => 'FedEx Overnight Freight',
            'FEDEX_2_DAY_FREIGHT' => 'FedEx 2 day Freight',
            'FEDEX_3_DAY_FREIGHT' => 'FedEx 3 day Freight',
            'FEDEX_GROUND' => 'FedEx Ground',
            'GROUND_HOME_DELIVERY' => 'FedEx Home Delivery',
            'INTERNATIONAL_PRIORITY_FREIGHT' => 'FedEx International Priority Freight',
            'INTERNATIONAL_ECONOMY_FREIGHT' => 'FedEx International Economy Freight',
            'EUROPE_FIRST_INTERNATIONAL_PRIORITY' => 'FedEx Europe First International Priority',
        ];
    }

    /**
     * Get list of dropoff types.
     *
     * @return array
     */
    public static function getDropoffTypes()
    {
        return [
            'REGULAR_PICKUP' => 'Regular Pickup',
            'REQUEST_COURIER' => 'Request Courier',
            'DROPBOX' => 'Drop Box',
            'BUSINESS_SERVICE_CENTER' => 'Business Service Center',
            'STATION' => 'Station',
        ];
    }

    /**
     * Get list of packaging types.
     *
     * @return array
     */
    public static function getPackagingTypes()
    {
        return [
            'YOUR_PACKAGING' => 'Your Own Packaging',
            'FEDEX_ENVELOPE' => 'FedEx Envelope',
            'FEDEX_PAK' => 'FedEx Pak',
            'FEDEX_BOX' => 'FedEx Box',
            'FEDEX_TUBE' => 'FedEx Tube',
            'FEDEX_10KG_BOX' => 'FedEx 10Kg Box',
            'FEDEX_25KG_BOX' => 'Fedex 25Kg Box',
        ];
    }

    /**
     * Get list of weight units.
     *
     * @return array
     */
    public static function getWeightUnits()
    {
        return [
            'LB' => 'Pounds',
            'KG' => 'Kilograms',
        ];
    }
}
