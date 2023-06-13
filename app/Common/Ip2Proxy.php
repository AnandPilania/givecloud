<?php

namespace Ds\Common;

use Ds\Domain\Shared\Exceptions\MessageException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use IP2Proxy\Database;

class Ip2Proxy
{
    /** @var \IP2Proxy\Database|null */
    protected $database;

    /** @var string */
    protected $databasePath;

    /** @var \Illuminate\Http\Request */
    protected $request;

    /**
     * Create a new Ip2Proxy instance.
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
     * Get proxy data for an IP.
     *
     * @param string $ip
     * @return object
     */
    public function getProxyData($ip = null)
    {
        $ip = $ip ?? $this->request->ip();

        $record = $this->getDatabase()->getAll($ip);

        if (Arr::get($record, 'isProxy') === -1) {
            throw new MessageException('Error occurred while looking up proxy data');
        }

        return (object) [
            'ip' => $ip,
            'is_proxy' => Arr::get($record, 'isProxy') > 0,
            'is_datacenter' => Arr::get($record, 'isProxy') === 2,
            'proxy_type' => trim(Arr::get($record, 'proxyType'), '-') ?: null,
            'iso_code' => trim(Arr::get($record, 'countryCode'), '-') ?: null,
            'country' => trim(Arr::get($record, 'countryName'), '-') ?: null,
            'city' => trim(Arr::get($record, 'cityName'), '-') ?: null,
            'state' => trim(Arr::get($record, 'regionName'), '-') ?: null,
            'isp' => trim(Arr::get($record, 'isp'), '-') ?: null,
        ];
    }

    /**
     * Get an instance of the database.
     *
     * @return \IP2Proxy\Database
     */
    private function getDatabase()
    {
        if (empty($this->database)) {
            $this->database = new Database;
            $this->database->open($this->databasePath, Database::FILE_IO);
        }

        return $this->database;
    }
}
