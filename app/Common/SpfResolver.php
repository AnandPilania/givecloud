<?php

namespace Ds\Common;

use Illuminate\Support\Str;
use Rephlux\SpfResolver\SpfResolver as RephluxSpfResolver;

class SpfResolver extends RephluxSpfResolver
{
    /** @var bool */
    protected $fail = false;

    /** @var array */
    protected $spfData = [];

    /** @var array */
    protected $includes = [];

    /**
     * Create an instance.
     *
     * @param string $hostname
     */
    public function __construct($hostname = null)
    {
        if ($hostname) {
            $this->resolveDomain($hostname);
        }
    }

    /**
     * Load ip addresses from a spf record for a specific hostname.
     *
     * @param string $hostname
     * @return array
     */
    public function resolveDomain($hostname)
    {
        $dnsData = $this->getDnsRecord($hostname);

        if ($this->hasDnsData($dnsData) === false) {
            return [];
        }

        $spfData = $this->extractSpfRecord($dnsData);

        if ($this->hasValidSpfData($spfData) === false) {
            return $this->getIpAddresses();
        }

        return $this->extractIpAdresses()
            ->followRedirects()
            ->extractInclude()
            ->getIpAddresses();
    }

    /**
     * Get a unique list of all the includes.
     *
     * @return array
     */
    public function getIncludes()
    {
        return array_values(array_unique($this->includes));
    }

    /**
     * Check if the SPF has a failure policy.
     *
     * @return bool
     */
    public function hasFailurePolicy()
    {
        return $this->fail;
    }

    /**
     * Get the spf data.
     *
     * @return mixed
     */
    public function getSpfData($all = false)
    {
        return $all ? $this->spfData : end($this->spfData);
    }

    /**
     * Resets the internal ipAddresses and includes arrays to empty arrays
     */
    public function resetResolvedIPs()
    {
        $this->fail = false;
        $this->includes = [];
        $this->ipAddresses = [];
        $this->spfData = [];
    }

    /**
     * Extract spf includes from dns data.
     *
     * @return $this
     */
    protected function extractInclude()
    {
        $spfData = $this->extractDnsData(self::$regexInclude, $this->getSpfData());

        $this->includes = array_merge($this->includes, $spfData);

        $this->ipAddresses = $this->mergeIpAddresses(
            $this->getIpAddresses(),
            $spfData
        );

        return $this;
    }

    /**
     * Gets the spf data from dns record.
     *
     * @param array $dnsData
     * @return mixed
     */
    protected function extractSpfRecord($dnsData)
    {
        $spfData = parent::extractSpfRecord($dnsData);

        if ($this->hasValidSpfData($spfData)) {
            if (Str::contains($spfData, '-all')) {
                $this->fail = true;
            }
        }

        return $this->spfData[] = $spfData;
    }
}
