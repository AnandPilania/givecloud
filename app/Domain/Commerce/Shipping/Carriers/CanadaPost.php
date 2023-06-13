<?php

namespace Ds\Domain\Commerce\Shipping\Carriers;

use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Shipping\AbstractCarrier;
use Ds\Domain\Commerce\Shipping\Rate;
use Ds\Domain\Commerce\Shipping\ShipmentOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Spatie\ArrayToXml\ArrayToXml;
use Throwable;

class CanadaPost extends AbstractCarrier
{
    const ENDPOINT_TESTING = 'https://ct.soa-gw.canadapost.ca/rs/ship/price';
    const ENDPOINT_PRODUCTION = 'https://soa-gw.canadapost.ca/rs/ship/price';

    /** @var string */
    private $customerNumber;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var bool */
    private $liveMode;

    /**
     * Create an instance.
     *
     * @param string $customerNumber
     * @param string $username
     * @param string $password
     * @param bool $liveMode
     */
    public function __construct($customerNumber, $username, $password, $liveMode = true)
    {
        $this->customerNumber = $customerNumber;
        $this->username = $username;
        $this->password = $password;
        $this->liveMode = (bool) $liveMode;
    }

    /**
     * Get the carrier handle.
     *
     * @return string
     */
    public function getHandle(): string
    {
        return 'canadapost';
    }

    /**
     * Get the carrier name.
     *
     * @return string
     */
    public function getName(): string
    {
        return sys_get('locale') == 'fr-CA' ? 'Postes Canada' : 'Canada Post';
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

        // postal codes to have no spaces and be uppercase
        $from_zip = str_replace(' ', '', strtoupper($from_zip));
        $ship_zip = str_replace(' ', '', strtoupper($ship_zip));

        // weight must be in kilograms
        if (strtoupper($options->weight_type) === 'LB') {
            $weight = round($weight * 0.453592, 2);
        }

        // from zip required
        if (empty($from_zip)) {
            return $rates;
        }

        // ship country required
        if (empty($ship_country)) {
            return $rates;
        }

        // ship zip required for destinations in Canada and the US
        if (empty($ship_zip) && in_array($ship_country, ['CA', 'US'])) {
            return $rates;
        }

        $data = [
            '_attributes' => [
                'xmlns' => 'http://www.canadapost.ca/ws/ship/rate-v4',
            ],
            'customer-number' => $this->customerNumber,
            'parcel-characteristics' => [
                'weight' => $weight,
            ],
            'origin-postal-code' => $from_zip,
            'destination' => [
                'international' => [
                    'country-code' => $ship_country,
                ],
            ],
        ];

        if ($ship_country === 'CA') {
            $data['destination'] = [
                'domestic' => ['postal-code' => $ship_zip],
            ];
        } elseif ($ship_country === 'US') {
            $data['destination'] = [
                'united-states' => ['zip-code' => $ship_zip],
            ];
        }

        $xmlRequest = ArrayToXml::convert($data, 'mailing-scenario', true, 'UTF-8');

        try {
            $res = Http::withOptions([
                'connect_timeout' => 1,
                'timeout' => 5,
                'verify' => true,
            ])->withHeaders([
                'Accept' => 'application/vnd.cpc.ship.rate-v4+xml',
                'Accept-Language' => sys_get('locale') == 'fr-CA' ? 'fr-CA' : 'en-CA',
            ])->withBasicAuth($this->username, $this->password)
                ->withBody($xmlRequest, 'application/vnd.cpc.ship.rate-v4+xml')
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

        foreach ($res->{'price-quote'} as $quote) {
            $rates[] = new Rate(
                $this,
                (string) $quote->{'service-code'},
                (string) $quote->{'service-name'},
                new Money((float) $quote->{'price-details'}->due, 'CAD')
            );
        }

        if (isset($res->message->description)) {
            notifyError('ShippingError', (string) $res->message->description, function ($report) use ($xmlRequest) {
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
     * @see https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/rating/getrates/default.jsf
     *
     * @return array
     */
    public static function getServices(): array
    {
        return [
            'DOM.EP' => 'Expedited Parcel',
            'DOM.RP' => 'Regular Parcel',
            'DOM.PC' => 'Priority',
            'DOM.XP' => 'Xpresspost',
            'DOM.XP.CERT' => 'Xpresspost Certified',
            'DOM.LIB' => 'Library Materials',
            'USA.EP' => 'Expedited Parcel USA',
            'USA.PW.ENV' => 'Priority Worldwide Envelope USA',
            'USA.PW.PAK' => 'Priority Worldwide pak USA',
            'USA.PW.PARCEL' => 'Priority Worldwide Parcel USA',
            'USA.SP.AIR' => 'Small Packet USA Air',
            'USA.TP' => 'Tracked Packet – USA',
            'USA.TP.LVM' => 'Tracked Packet – USA (LVM) (large volume mailers)',
            'USA.XP' => 'Xpresspost USA',
            'INT.XP' => 'Xpresspost International',
            'INT.IP.AIR' => 'International Parcel Air',
            'INT.IP.SURF' => 'International Parcel Surface',
            'INT.PW.ENV' => 'Priority Worldwide Envelope Int’l',
            'INT.PW.PAK' => 'Priority Worldwide pak Int’l',
            'INT.PW.PARCEL' => 'Priority Worldwide parcel Int’l',
            'INT.SP.AIR' => 'Small Packet International Air',
            'INT.SP.SURF' => 'Small Packet International Surface',
            'INT.TP' => 'Tracked Packet – International',
        ];
    }
}
