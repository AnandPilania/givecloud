<?php

namespace Ds\Services;

use Ds\Domain\Commerce\Currency;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Http\Request;

class GeoIpService
{
    /** @var string */
    protected $databasePath;

    /** @var \GeoIp2\Database\Reader|null */
    protected $reader;

    /** @var \Illuminate\Http\Request */
    protected $request;

    /**
     * Create a new GeoIP instance.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $databasePath
     */
    public function __construct(Request $request, string $databasePath)
    {
        $this->request = $request;
        $this->databasePath = $databasePath;
    }

    /**
     * @param string $key
     * @param string $ip
     * @param null $default
     * @return array|mixed
     */
    public function get(string $key, string $ip, $default = null)
    {
        $location = rescue(function () use ($ip) {
            return $this->getLocationData($ip);
        }, null, false);

        return data_get($location, $key, $default);
    }

    /**
     * Get location data for an IP.
     *
     * @param string $ip
     * @return object
     */
    public function getLocationData($ip = null): object
    {
        $ip = $ip ?? $this->request->ip();

        if ($this->isValidIp($ip) === false) {
            throw new AddressNotFoundException;
        }

        $record = $this->getReader()->city($ip);

        return (object) [
            'ip' => $ip,
            'iso_code' => $record->country->isoCode,
            'country' => $record->country->name,
            'city' => $record->city->name,
            'state' => $record->mostSpecificSubdivision->isoCode,
            'postal_code' => $record->postal->code,
            'lat' => $record->location->latitude,
            'lon' => $record->location->longitude,
            'timezone' => $record->location->timeZone,
            'continent' => $record->continent->code,
            'default' => false,
            'currency' => $this->getCurrencyForCountry($record->country->isoCode),
        ];
    }

    /**
     * Get an instance of the reader.
     *
     * @return \GeoIp2\Database\Reader
     */
    private function getReader(): Reader
    {
        if (empty($this->reader)) {
            $this->reader = new Reader($this->databasePath);
        }

        return $this->reader;
    }

    /**
     * Checks if an ip is valid (ie. not private and not reserved).
     *
     * @return bool
     */
    private function isValidIp($ip): bool
    {
        if (empty($ip)) {
            return false;
        }

        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    private function getCurrencyForCountry(?string $isoCode): ?string
    {
        $currency = collect(Currency::getCurrencies())->first(function ($currency) use ($isoCode) {
            return in_array($isoCode, $currency['countries'], true);
        });

        return data_get($currency, 'code');
    }
}
