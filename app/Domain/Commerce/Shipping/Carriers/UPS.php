<?php

namespace Ds\Domain\Commerce\Shipping\Carriers;

use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Shipping\AbstractCarrier;
use Ds\Domain\Commerce\Shipping\Rate;
use Ds\Domain\Commerce\Shipping\ShipmentOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Spatie\ArrayToXml\ArrayToXml;
use Throwable;

class UPS extends AbstractCarrier
{
    const ENDPOINT_TESTING = 'https://wwwcie.ups.com/ups.app/xml/Rate';
    const ENDPOINT_PRODUCTION = 'https://onlinetools.ups.com/ups.app/xml/Rate';

    /** @var string */
    private $accessKey;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var array */
    private $serviceCodes;

    /** @var string */
    private $accountNumber;

    /** @var bool */
    private $negotiatedRates;

    /** @var bool */
    private $liveMode;

    /**
     * Create an instance.
     *
     * @param string $accessKey
     * @param string $username
     * @param string $password
     * @param array $serviceCodes
     * @param string $accountNumber
     * @param bool $negotiatedRates
     * @param bool $liveMode
     */
    public function __construct(
        $accessKey,
        $username,
        $password,
        array $serviceCodes = [],
        $accountNumber = null,
        $negotiatedRates = false,
        $liveMode = true
    ) {
        $this->accessKey = $accessKey;
        $this->username = $username;
        $this->password = $password;
        $this->serviceCodes = $serviceCodes;
        $this->accountNumber = $accountNumber;
        $this->negotiatedRates = (bool) $negotiatedRates;
        $this->liveMode = (bool) $liveMode;
    }

    /**
     * Get the carrier handle.
     *
     * @return string
     */
    public function getHandle(): string
    {
        return 'ups';
    }

    /**
     * Get the carrier name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'UPS';
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

        $from_state = $options->from_state;
        $from_zip = $options->from_zip;
        $from_country = $options->from_country;
        $ship_state = $options->ship_state;
        $ship_zip = $options->ship_zip;
        $ship_country = $options->ship_country;
        $weight = max(0.1, round($options->weight, 2));
        $weight_type = $options->weight_type;

        // The from and ship state, zip and country are required
        if (empty($from_state) || empty($from_zip) || empty($from_country) || empty($ship_state) || empty($ship_zip) || empty($ship_country)) {
            return $rates;
        }

        // Conform the weight type values
        if ($weight_type === 'LB') {
            $weight_type = 'LBS';
        }

        $data = [
            '_attributes' => [
                'xml:lang' => 'en-US',
            ],
            'AccessLicenseNumber' => $this->accessKey,
            'UserId' => $this->username,
            'Password' => $this->password,
        ];

        $xmlRequest = ArrayToXml::convert($data, 'AccessRequest', true, 'UTF-8');

        $data = [
            '_attributes' => [
                'xml:lang' => 'en-US',
            ],
            'Request' => [
                'TransactionReference' => [
                    'CustomerContext' => 'Rating and Service',
                    'XpciVersion' => '1.0',
                ],
                'RequestAction' => 'Rate',
                'RequestOption' => 'Shop',
            ],
            'PickupType' => [
                'Code' => '01',
            ],
            'Shipment' => [
                'Shipper' => [
                    'Address' => [
                        'StateProvinceCode' => $from_state,
                        'PostalCode' => $from_zip,
                        'CountryCode' => $from_country,
                    ],
                ],
                'ShipFrom' => [
                    'Address' => [
                        'StateProvinceCode' => $from_state,
                        'PostalCode' => $from_zip,
                        'CountryCode' => $from_country,
                    ],
                ],
                'ShipTo' => [
                    'Address' => [
                        'StateProvinceCode' => $ship_state,
                        'PostalCode' => $ship_zip,
                        'CountryCode' => $ship_country,
                        'ResidentialAddressIndicator' => 1,
                    ],
                ],
                'Service' => [
                    'Code' => 'EXPRESS',
                ],
                'Package' => [
                    'PackagingType' => [
                        'Code' => '02',
                    ],
                    'PackageWeight' => [
                        'UnitOfMeasurement' => [
                            'Code' => $weight_type,
                        ],
                        'Weight' => $weight,
                    ],
                ],
            ],
        ];

        if ($this->accountNumber) {
            $data['Shipment']['Shipper']['ShipperNumber'] = $this->accountNumber;
        }

        if ($this->negotiatedRates) {
            $data['Shipment']['RateInformation'] = [
                'NegotiatedRatesIndicator' => [],
            ];
        }

        $dom = (new ArrayToXml($data, 'RatingServiceSelectionRequest', true, 'UTF-8'))->toDom();
        $xmlRequest .= $dom->saveXML($dom->documentElement);

        try {
            $res = Http::withOptions([
                'connect_timeout' => 1,
                'timeout' => 5,
                'verify' => true,
            ])->withBody($xmlRequest, 'application/xml')
                ->post($this->getEndpoint())
                ->throw()
                ->xml();
        } catch (Throwable $e) {
            notifyError('ShippingError', $e->getMessage(), function ($report) use ($xmlRequest) {
                $report->setMetaData([
                    'xml_request' => $xmlRequest,
                ]);
            });

            return collect();
        }

        if ($res === false) {
            return $rates;
        }

        if ($res->Response->ResponseStatusCode == '1') {
            foreach ($res->RatedShipment as $service) {
                $code = (string) $service->Service->Code;

                if (count($this->serviceCodes) && ! in_array($code, $this->serviceCodes, true)) {
                    continue;
                }

                $amount = $service->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue ?? $service->TotalCharges->MonetaryValue;
                $currency = $service->NegotiatedRates->NetSummaryCharges->GrandTotal->CurrencyCode ?? $service->TotalCharges->CurrencyCode ?? 'USD';

                $rates[] = new Rate(
                    $this,
                    $code,
                    Arr::get(static::getServices(), $code, $code),
                    new Money((float) $amount, $currency)
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
            '14' => 'Next Day Air Early AM',
            '01' => 'Next Day Air',
            '13' => 'Next Day Air Saver',
            '59' => '2nd Day Air AM',
            '02' => '2nd Day Air',
            '12' => '3 Day Select',
            '03' => 'Ground',
            '11' => 'Standard',
            '07' => 'Worldwide Express',
            '54' => 'Worldwide Express Plus',
            '08' => 'Worldwide Expedited',
            '65' => 'Saver',
            '82' => 'UPS Today Standard',
            '83' => 'UPS Today Dedicated Courier',
            '84' => 'UPS Today Intercity',
            '85' => 'UPS Today Express',
            '86' => 'UPS Today Express Saver',
            '92' => 'UPS SurePost Less than 1LB',
            '93' => 'UPS SurePost 1LB or greater',
            '94' => 'UPS SurePost BPM',
            '95' => 'UPS SurePost Media Mail',
        ];
    }

    /**
     * Get list of pickup types.
     *
     * @return array
     */
    public static function getPickupTypes()
    {
        return [
            '01' => 'Daily Pickup',
            '03' => 'Customer Counter',
            '06' => 'One Time Pickup',
            '07' => 'On Call Air',
            '11' => 'Suggested Retail Rates',
            '19' => 'Letter Center',
            '20' => 'Air Service Center',
        ];
    }
}
