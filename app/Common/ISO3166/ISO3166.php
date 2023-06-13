<?php

namespace Ds\Common\ISO3166;

use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Collection;

class ISO3166
{
    protected $countryNameMappings = [];

    protected $ofacCountries = [
        'BY',
        'CD',
        'CI',
        'CG',
        'CU',
        'IQ',
        'IR',
        'KP',
        'LR',
        'NI',
        'MM',
        'RS',
        'SD',
        'SY',
        'ZW',
        'BI',
        'CF',
        'HK',
        'LB',
        'LY',
        'ML',
        'KP',
        'SO',
        'SS',
        'UA',
        'RU',
        'VE',
        'YE',
    ];

    protected $usApoDesignatedStates = [
        ['type' => 'State', 'code' => 'US-AA', 'name' => 'Armed Forces of the Americas'],
        ['type' => 'State', 'code' => 'US-AE', 'name' => 'Armed Forces of Europe'],
        ['type' => 'State', 'code' => 'US-AP', 'name' => 'Armed Forces of the Pacific'],
    ];

    public function __construct(Translator $translator)
    {
        $this->countryNameMappings = $translator->get('countries');
    }

    /**
     * Get data related to an ISO3166 countries.
     *
     * @return array
     */
    public function countries()
    {
        $restrictedCountries = $this->getRestrictedCountries();

        return (new Collection(dataset('iso.3166_1')))
            ->reject(function ($country) use ($restrictedCountries) {
                return in_array($country['alpha_2'] ?? '', $restrictedCountries, true);
            })->map(function ($country) {
                return $this->localizeCountryName($country);
            })->sortBy('name')
            ->values()
            ->all();
    }

    /**
     * Get data related to an ISO3166 country.
     *
     * @param string $country
     * @param string|null $attr
     * @return array|string|null
     */
    public function country(?string $country, $attr = null)
    {
        $countryName = $this->findCountryInMappings((string) $country);

        try {
            $countryData = $this->localizeCountryName($this->getCountryDataByNameOrCode($countryName));
        } catch (\Throwable $e) {
            return null;
        }

        if ($attr) {
            return $countryData[$attr] ?? null;
        }

        return $countryData;
    }

    public function countryForIp(string $ipAddress = null): ?string
    {
        try {
            $locationData = app('geoip')->getLocationData($ipAddress);
        } catch (AddressNotFoundException $e) {
            return sys_get('default_country');
        }

        return $this->country($locationData->iso_code)['alpha_2'] ?? sys_get('default_country');
    }

    /**
     * Get data related to an ISO3166 country subdivisions.
     */
    public function subdivisions(?string $value): array
    {
        $country = $this->country($value);

        if (empty($country)) {
            return [];
        }

        return collect(dataset('iso.3166_2'))
            ->filter(function ($subdivision) use ($country) {
                return 0 === substr_compare($country['alpha_2'], $subdivision['code'], 0, 2, true);
            })->when(
                $country === 'US',
                fn ($subdivisions) => $subdivisions->merge($this->usApoDesignatedStates),
            )->sortBy('name')
            ->values()
            ->all();
    }

    /**
     * Get data related to an ISO3166 subdivision.
     *
     * @return array|string|null
     */
    public function subdivision(?string $value, ?string $attr = null)
    {
        $key = preg_match('/^[a-zA-Z]{2}-[a-zA-Z0-9]{1,3}$/', $value) ? 'code' : 'name';
        $data = null;

        foreach (dataset('iso.3166_2') as $subdivision) {
            if (0 === strcasecmp($value, $subdivision[$key] ?? '')) {
                $data = $subdivision;
            }
        }

        if ($attr) {
            return $data[$attr] ?? null;
        }

        return $data;
    }

    /**
     * Expand an abbreviated address string.
     *
     * @param string $address
     * @param bool $stateOnly
     * @return string
     */
    public function expandAbbr(?string $address, bool $stateOnly = false)
    {
        if (preg_match('/\b([a-z]{2}),\s*([a-z]{2})\b/i', $address, $matches)) {
            $country = $this->country($matches[2]);
            $subdivision = $this->subdivision($matches[2] . '-' . $matches[1]);

            if ($country && $subdivision) {
                $address = str_replace(
                    $matches[0],
                    $subdivision['name'] . ', ' . ($stateOnly ? $country['alpha_2'] : $country['name']),
                    $address
                );
            }
        }

        return $address;
    }

    protected function findCountryInMappings(string $countryName)
    {
        // Find the country array key from its name
        $countryKey = array_search(
            strtoupper($countryName),
            array_map('mb_strtoupper', array_values($this->countryNameMappings)),
            true
        );

        if ($countryKey) {
            return array_keys($this->countryNameMappings)[$countryKey];
        }

        return $countryName;
    }

    protected function getCountryDataByNameOrCode(string $value): ?array
    {
        $key = 'name';

        if (is_numeric($value)) {
            $key = 'numeric';
        } elseif (strlen($value) === 2) {
            $key = 'alpha_2';
        } elseif (strlen($value) === 3) {
            $key = 'alpha_3';
        }

        foreach ($this->countries() as $country) {
            if (0 === strcasecmp($value, $country[$key] ?? '')) {
                return $country;
            }
        }

        return null;
    }

    protected function localizeCountryName(array $countryData): array
    {
        $name = ($this->countryNameMappings[$countryData['alpha_2']] ?? null) ?: null;

        return array_merge($countryData, compact('name'));
    }

    protected function getRestrictedCountries(): array
    {
        $restrictedCountries = $this->ofacCountries;

        if (in_array(sys_get('ds_account_name'), [
            'usanafoundation-hongkong',
            'us-give-bluedragon',
            'bluedragon',
            'au-bluedragon',
            'uk-bluedragon',
        ])) {
            $restrictedCountries = array_diff($restrictedCountries, ['HK']);
        }

        if (sys_get('ds_account_name') == 'bpsi') {
            $restrictedCountries = array_diff($restrictedCountries, ['IR']);
        }

        return $restrictedCountries;
    }
}
