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

class USPS extends AbstractCarrier
{
    const ENDPOINT_TESTING = 'https://testing.shippingapis.com/ShippingAPITest.dll';
    const ENDPOINT_PRODUCTION = 'https://production.shippingapis.com/ShippingAPI.dll';

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var array */
    private $classIds;

    /** @var array */
    private $internationalClassIds;

    /** @var bool */
    private $liveMode;

    /**
     * Create an instance.
     *
     * @param string $username
     * @param string $password
     * @param array $classIds
     * @param array $internationalClassIds
     * @param bool $liveMode
     */
    public function __construct($username, $password, array $classIds = [], array $internationalClassIds = [], $liveMode = true)
    {
        $this->username = $username;
        $this->password = $password;
        $this->classIds = $classIds;
        $this->internationalClassIds = $internationalClassIds;
        $this->liveMode = (bool) $liveMode;
    }

    /**
     * Get the carrier handle.
     *
     * @return string
     */
    public function getHandle(): string
    {
        return 'usps';
    }

    /**
     * Get the carrier name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'USPS';
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

        $from_zip = $options->from_zip;
        $ship_zip = $options->ship_zip;
        $ship_country = $options->ship_country;
        $weight = max(0.1, round($options->weight, 2));
        $contents_value = $options->contents_value;

        // Only 5-digit zip codes are supported
        $from_zip = substr($from_zip, 0, 5);
        $ship_zip = substr($ship_zip, 0, 5);

        // Convert country codes into country names
        $ship_country = Arr::get(app('iso3166')->country($ship_country), 'name', $ship_country);

        // Convert pounds into pounds and ounces
        $pounds = floor($weight);
        $ounces = ($weight - $pounds) * 16;

        // from zip required
        if (empty($from_zip)) {
            return $rates;
        }

        // ship country required
        if (empty($ship_country)) {
            return $rates;
        }

        // ship zip required for destinations in US
        if (empty($ship_zip) && $ship_country === 'United States') {
            return $rates;
        }

        if ($ship_country === 'United States') {
            $api = 'RateV4';
            $data = [
                '_attributes' => [
                    'USERID' => $this->username,
                ],
                'Package' => [
                    '_attributes' => [
                        'ID' => 0,
                    ],
                    'Service' => 'ALL',
                    'ZipOrigination' => $from_zip,
                    'ZipDestination' => $ship_zip,
                    'Pounds' => $pounds,
                    'Ounces' => $ounces,
                    'Container' => '',
                    'Size' => 'REGULAR',
                    'Machinable' => 'true',
                ],
            ];

            $rootElement = 'RateV4Request';
        } else {
            $api = 'IntlRateV2';
            $data = [
                '_attributes' => [
                    'USERID' => $this->username,
                ],
                'Revision' => 2,
                'Package' => [
                    '_attributes' => [
                        'ID' => 0,
                    ],
                    'Pounds' => $pounds,
                    'Ounces' => $ounces,
                    'Machinable' => 'true',
                    'MailType' => 'Package',
                    'ValueOfContents' => $contents_value,
                    'Country' => $ship_country,
                    'Container' => '',
                    'Size' => 'REGULAR',
                    'Width' => 0,
                    'Length' => 0,
                    'Height' => 0,
                    'Girth' => 0,
                    'OriginZip' => $from_zip,
                    'CommercialFlag' => 'N',
                ],
            ];

            $rootElement = 'IntlRateV2Request';
        }

        $xmlRequest = ArrayToXml::convert($data, $rootElement, true, 'UTF-8');

        try {
            $res = Http::withOptions([
                'connect_timeout' => 1,
                'timeout' => 5,
                'verify' => true,
            ])->get($this->getEndpoint(), [
                'API' => $api,
                'XML' => $xmlRequest,
            ])->throw()
                ->xml();
        } catch (Throwable $e) {
            notifyError('ShippingError', $e->getMessage(), function ($report) use ($xmlRequest) {
                $report->setMetaData([
                    'xml_request' => $xmlRequest,
                ]);
            });

            return $rates;
        }

        if ($res === false) {
            return $rates;
        }

        if ($ship_country == 'United States') {
            if (isset($res->Package->Postage)) {
                foreach ($res->Package->Postage as $service) {
                    $code = (string) $service['CLASSID'];
                    $name = (string) $service->MailService;
                    $name = preg_replace('/&lt;(.*)&gt;/is', '', $name);

                    // Stripping out variables added that depend on the scheduled delivery date
                    // calculation between the origin and destination ZIP Codes.
                    // https://docs.rocketship.it/php/1-0/usps-class-ids.html
                    $name = preg_replace('/\s+(1-Day|2-Day|3-Day|Military|DPO)/', '', $name);

                    // Fix duplicate class IDs
                    if ($code === '0') {
                        if ($name == 'First-Class Mail Large Envelope') {
                            $code = '00';
                        }

                        if ($name == 'First-Class Mail Letter') {
                            $code = '01';
                        }

                        if ($name == 'First-Class Mail Stamped Letter') {
                            $code = '01';
                            $name = 'First-Class Letter';
                        }

                        if ($name == 'First-Class Mail Parcel') {
                            $code = '02';
                        }

                        if ($name == 'First-Class Mail Postcards') {
                            $code = '03';
                        }

                        if ($name == 'First-Class Package Service - Retail') {
                            $code = '04';
                            $name = 'First-Class Package Service';
                        }
                    }

                    if (count($this->classIds) && ! in_array($code, $this->classIds, true)) {
                        continue;
                    }

                    $rates[] = new Rate($this, $code, $name, new Money((float) $service->Rate, 'USD'));
                }
            }
        } else {
            if (isset($res->Package->Service)) {
                foreach ($res->Package->Service as $service) {
                    $code = (string) $service['ID'];
                    $name = (string) $service->SvcDescription;
                    $name = preg_replace('/&lt;(.*)&gt;/is', '', $name);

                    if (count($this->internationalClassIds) && ! in_array($code, $this->internationalClassIds, true)) {
                        continue;
                    }

                    $rates[] = new Rate($this, $code, $name, new Money((float) $service->Postage, 'USD'));
                }
            }
        }

        // IntlRateV2 errors
        if (isset($res->Package->Error)) {
            $res = $res->Package->Error;
        }

        // RateV4 errors
        if (isset($res->Description)) {
            notifyError('ShippingError', (string) $res->Description, function ($report) use ($xmlRequest) {
                $report->setMetaData([
                    'xml_request' => $xmlRequest,
                ]);
            });
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
            'FIRST CLASS' => 'First Class',
            'FIRST CLASS COMMERCIAL' => 'First Class Commercial',
            'FIRST CLASS HFP COMMERCIAL' => 'First Class HFP Commercial',
            'FIRST CLASS METERED' => 'First Class Metered',
            'PRIORITY' => 'Priority',
            'PRIORITY COMMERCIAL' => 'Priority Commercial',
            'PRIORITY CPP' => 'Priority CPP',
            'PRIORITY HFP COMMERCIAL' => 'Priority HFP Commercial',
            'PRIORITY HFP CPP' => 'Priority CPP',
            'EXPRESS' => 'Express',
            'EXPRESS COMMERCIAL' => 'Express Commercial',
            'EXPRESS CPP' => 'Express CPP',
            'EXPRESS SH' => 'Express SH',
            'EXPRESS SH COMMERCIAL' => 'Express SH Commercial',
            'EXPRESS HFP' => 'Express HFP',
            'EXPRESS HFP COMMERCIAL' => 'Express HFP Commercial',
            'EXPRESS HFP CPP' => 'Express HFP CPP',
            'STANDARD POST' => 'Standard Post',
            'MEDIA' => 'Media Mail',
            'LIBRARY' => 'Library Mail',
            'ONLINE' => 'Online',
            'PLUS' => 'Plus',
        ];
    }

    /**
     * Get list of first class mail types.
     *
     * @return array
     */
    public static function getFirstClassMailTypes()
    {
        return [
            'FLAT' => 'Flat',
            'LETTER' => 'Letter',
            'PARCEL' => 'Parcel',
            'POSTCARD' => 'Postcard',
        ];
    }

    /**
     * Get list of class ids.
     *
     * @return array
     */
    public static function getClassIds()
    {
        return [
            '3' => 'Express Mail',
            '2' => 'Express Mail Hold For Pickup',
            '55' => 'Express Mail Flat Rate Boxes',
            '56' => 'Express Mail Flat Rate Boxes Hold For Pickup',
            '13' => 'Express Mail Flat Rate Envelope',
            '27' => 'Express Mail Flat Rate Envelope Hold For Pickup',
            '30' => 'Express Mail Legal Flat Rate Envelope',
            '31' => 'Express Mail Legal Flat Rate Envelope Hold For Pickup',
            '62' => 'Express Mail Padded Flat Rate Envelope',
            '63' => 'Express Mail Padded Flat Rate Envelope Hold For Pickup',
            '1' => 'Priority Mail',
            '22' => 'Priority Mail Large Flat Rate Box',
            '17' => 'Priority Mail Medium Flat Rate Box',
            '28' => 'Priority Mail Small Flat Rate Box',
            '16' => 'Priority Mail Flat Rate Envelope',
            '44' => 'Priority Mail Legal Flat Rate Envelope',
            '29' => 'Priority Mail Padded Flat Rate Envelope',
            '38' => 'Priority Mail Gift Card Flat Rate Envelope',
            '42' => 'Priority Mail Small Flat Rate Envelope',
            '40' => 'Priority Mail Window Flat Rate Envelope',
            '00' => 'First-Class Mail Large Envelope',
            '01' => 'First-Class Mail Letter',
            '02' => 'First-Class Mail Parcel',
            '03' => 'First-Class Mail Postcards',
            '78' => 'First-Class Metered Mail',
            '04' => 'First-Class Package Service',
            '4' => 'Standard Post',
            '6' => 'Media Mail',
            '7' => 'Library Mail',
        ];
    }

    /**
     * Get list of international class ids.
     *
     * @return array
     */
    public static function getInternationalClassIds()
    {
        return [
            '1' => 'Priority Mail Express International',
            '2' => 'Priority Mail International',
            '4' => 'Global Express Guaranteed (GXG)',
            '5' => 'Global Express Guaranteed Document',
            '6' => 'Global Express Guaranteed Non-Document Rectangular',
            '7' => 'Global Express Guaranteed Non-Document Non-Rectangular',
            '8' => 'Priority Mail International Flat Rate Envelope',
            '9' => 'Priority Mail International Medium Flat Rate Box',
            '10' => 'Priority Mail Express International Flat Rate Envelope',
            '11' => 'Priority Mail International Large Flat Rate Box',
            '12' => 'USPS GXG Envelopes',
            '13' => 'First-Class Mail International Letter',
            '14' => 'First-Class Mail International Large Envelope',
            '15' => 'First-Class Package International Service',
            '16' => 'Priority Mail International Small Flat Rate Box',
            '17' => 'Priority Mail Express International Legal Flat Rate Envelope',
            '18' => 'Priority Mail International Gift Card Flat Rate Envelope',
            '19' => 'Priority Mail International Window Flat Rate Envelope',
            '20' => 'Priority Mail International Small Flat Rate Envelope',
            '21' => 'First-Class Mail International Postcard',
            '22' => 'Priority Mail International Legal Flat Rate Envelope',
            '23' => 'Priority Mail International Padded Flat Rate Envelope',
            '24' => 'Priority Mail International DVD Flat Rate priced box',
            '25' => 'Priority Mail International Large Video Flat Rate priced box',
            '26' => 'Priority Mail Express International Flat Rate Boxes',
            '27' => 'Priority Mail Express International Padded Flat Rate Envelope',
        ];
    }
}
