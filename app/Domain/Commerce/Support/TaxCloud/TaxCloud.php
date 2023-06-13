<?php

namespace Ds\Domain\Commerce\Support\TaxCloud;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TaxCloud
{
    /** @var string Endpoint for the TaxCloud api calls */
    protected $endpoint;

    /** @var string */
    protected $api_key;

    /** @var string */
    protected $api_login_id;

    /**
     * @param array $opts Options for setting up this TaxCloud instance
     */
    public function __construct($opts = [])
    {
        $opts += [
            'endpoint' => 'https://api.taxcloud.net/1.0/TaxCloud/',
            'api_key' => '',
            'api_login_id' => '',
        ];

        $this->endpoint = $opts['endpoint'];
        $this->api_key = $opts['api_key'];
        $this->api_login_id = $opts['api_login_id'];
    }

    /**
     * Ping the TaxCloud API service
     *
     * @return object Deserialized JSON from TaxCloud
     */
    public function ping()
    {
        return $this->request('Ping');
    }

    /**
     * Return a list of tax cloud
     *
     * @return array List of TICs
     */
    public function getTICs()
    {
        $tics = $this->request('GetTICs')->TICs;

        // TaxlCoud returns TIC's as INTs in the JSON format.
        // BUT - one of their most important codes is `00000`
        // (general, taxable goods) which converts to simply
        // 0 in JSON.  We need to find that TIC and convert
        // it to a (string) '00000' instead of (int) 0.
        foreach ($tics as $tic) {
            if ($tic->TICID == 0) {
                $tic->TICID = '00000';
            }
        }

        return $tics;
    }

    /**
     * Return tax rates for all items in cart
     *
     * @return array $data TaxCloud's list of cart items with rates
     */
    public function lookup($data)
    {
        $hash = sha1(json_encode($data));

        return Cache::remember("taxcloud-lookup:$hash", now()->addHours(24), function () use ($data) {
            app('log')->channel('taxcloud')->debug('taxcloud lookup', $data);

            return $this->request('Lookup', $data)->CartItemsResponse;
        });
    }

    /**
     * Authorizes and captures final tax amounts.
     *
     * @param array $data
     */
    public function authorizedWithCapture($data)
    {
        app('log')->channel('taxcloud')->debug('taxcloud auth/capture', $data);

        return $this->request('AuthorizedWithCapture', $data);
    }

    /**
     * Prepares the request for TaxCloud
     *
     * @param string $action the API action being run
     * @param array $fields any additional fields we're required for the API request
     * @return object deserialized JSON response from TaxCloud
     */
    protected function request($action, $fields = [])
    {
        $result = [];
        $credentials = [
            'apiLoginId' => $this->api_login_id,
        ];

        $response = Http::asJson()->acceptJson()->post(
            $this->_buildEndpoint($action),
            $credentials + $fields
        )->throw();

        // log response
        app('log')->channel('taxcloud')->debug('taxcloud response', [(string) $response->getBody()]);

        // get the json response
        $json = $response->object();

        // ResponseType = 3 means SUCCESS
        // catch errors
        if ($json->ResponseType != 3) {
            // prep an error message
            $messages = 'Unknown TaxCloud error. (response type ' . $json->ResponseType . ')';

            // if there are specific messages
            if (count($json->Messages)) {
                $messages = '';
                collect($json->Messages)->each(function ($item) use (&$messages) {
                    $messages .= $item->Message . ' ';
                });
            }

            // throw the exception
            throw new \DomainException('Error calculating tax. ' . $messages);
        }

        // return json payload
        return $json;
    }

    /**
     * Build the endpoint for the api request using the action
     * and the apikey
     *
     * @param string $action The API action being run
     */
    private function _buildEndpoint($action)
    {
        return $this->endpoint . $action . '?apiKey=' . $this->api_key;
    }
}
