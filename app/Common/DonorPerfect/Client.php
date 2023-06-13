<?php

namespace Ds\Common\DonorPerfect;

use Ds\Illuminate\Http\Client\XmlParseException;
use Exception;
use Illuminate\Http\Client\ConnectionException as ConnectionException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\RequestException as ClientRequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use SimpleXmlElement;
use Throwable;

class Client
{
    /** @var string */
    protected $endpoint = 'https://dpoapi.donorperfect.net/prod/xmlrequest.asp';

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $login;

    /** @var string */
    protected $pass;

    /** @var string */
    protected $partnerTag;

    /** @var string */
    protected $requestMethod = 'POST';

    /** @var int */
    protected $requestTimeout = 15;

    /** @var bool */
    protected $logging;

    /** @var \Illuminate\Http\Client\Response|null */
    protected $lastResponse;

    /** @var \Illuminate\Support\Collection|int|null */
    protected $lastResult;

    /** @var \Illuminate\Support\Collection */
    protected $lastErrors;

    /**
     * Create an instance.
     */
    public function __construct()
    {
        if (sys_get('dpo_request_url')) {
            $this->setEndpoint(sys_get('dpo_request_url'));
        }

        if (sys_get('dpo_api_key')) {
            $this->setApiKey(sys_get('dpo_api_key'));
        } elseif (sys_get('dpo_user')) {
            $this->setLogin(sys_get('dpo_user'));
            $this->setPassword(sys_get('dpo_pass'));
        }

        if (sys_get('dpo_partner_tag')) {
            $this->setPartnerTag(sys_get('dpo_partner_tag'));
        }

        if (sys_get('dp_request_method')) {
            $this->setRequestMethod(sys_get('dp_request_method'));
        }

        if (sys_get('dp_logging') == 1) {
            $this->setLogging(true);
        }
    }

    /**
     * Set the endpoint.
     *
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Set the login.
     *
     * @param string|null $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * Get the login.
     *
     * @return string|null
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set the password.
     *
     * @param string|null $password
     */
    public function setPassword($password)
    {
        $this->pass = $password;
    }

    /**
     * Get the password.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->pass;
    }

    /**
     * Set the api key.
     *
     * @param string|null $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get a fingerprint of the auth credentials.
     *
     * @return string|null
     */
    public function getAuthFingerpint(): ?string
    {
        if ($this->apiKey) {
            return sha1("apikey|{$this->apiKey}");
        }
        if ($this->login) {
            return sha1("login|{$this->login}:{$this->pass}");
        }

        return null;
    }

    /**
     * Set partner tag option.
     */
    public function setPartnerTag(string $partnerTag): void
    {
        $this->partnerTag = $partnerTag;
    }

    /**
     * Set the request method.
     *
     * @param string $requestMethod
     */
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = ($requestMethod === 'POST') ? 'POST' : 'GET';
    }

    /**
     * Set logging option.
     *
     * @param bool|null $logging
     */
    public function setLogging($logging)
    {
        $this->logging = (bool) $logging;
    }

    /**
     * Get the result of the last request
     *
     * @return \Illuminate\Support\Collection|int|string
     */
    public function getLastResult($raw = false)
    {
        if ($raw) {
            return (string) $this->lastResponse->getBody();
        }

        return $this->lastResult;
    }

    /**
     * Get any errors from the last request
     *
     * @return \Illuminate\Support\Collection|int
     */
    public function getLastErrors()
    {
        return $this->lastErrors;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function wrapWithAuth(array $data)
    {
        $credentials = [
            'login' => $this->login,
            'pass' => $this->pass,
        ];

        if ($this->apiKey) {
            // confirmed with DP dev team api keys provided by DP are not URL encoded but may contain % characters
            // which could cause potential %{20 > FF hex} collisions. in order to prevent any potential collisions
            // from borking the key when the query params are URL encoded we are URL decoding the key
            $credentials = ['apikey' => rawurldecode($this->apiKey)];
        }

        if ($this->partnerTag) {
            $credentials['@tag'] = "'{$this->partnerTag}'";
        }

        return array_merge($credentials, $data);
    }

    /**
     * Perform a DPO API request.
     *
     * @param string $action
     * @param string|array $params
     * @return \Illuminate\Support\Collection|int
     *
     * @throws \Ds\Common\DonorPerfect\RequestException
     */
    public function request($action, $params = [])
    {
        $this->lastResponse = null;
        $this->lastResult = null;
        $this->lastErrors = collect();

        // Prepare parameters
        if (is_array($params)) {
            foreach ($params as &$param) {
                if (is_int($param) || is_float($param)) {
                    $param = $this->escape($param);
                } elseif ($param === null || trim(strtoupper($param)) === 'NULL') {
                    $param = 'NULL';
                } else {
                    $param = "'" . $this->escape($param) . "'";
                }
            }
            $params = implode(',', $params);
        } elseif (isset($params) && ! is_string($params)) {
            app('activitron')->increment('Site.dpo.request.failure');
            throw new InvalidArgumentException;
        }

        // Make the request
        try {
            app('activitron')->startTiming('Site.dpo.request.time');

            if ($this->requestMethod === 'POST') {
                $this->lastResponse = Http::asForm()
                    ->withOptions([
                        'query' => $this->wrapWithAuth([]),
                        'timeout' => $this->requestTimeout,
                    ])->post($this->endpoint, [
                        'action' => $action,
                        'params' => $params,
                    ]);
            } else {
                $this->lastResponse = Http::asForm()
                    ->withOptions([
                        'timeout' => $this->requestTimeout,
                    ])->get($this->endpoint, $this->wrapWithAuth([
                        'action' => $action,
                        'params' => $params,
                    ]));
            }

            $xml = $this->lastResponse->throw()->xml();

            $this->logRequest($action, $params, null, [
                'duration' => app('activitron')->endTiming('Site.dpo.request.time'),
            ]);
        } catch (ConnectionException $e) {
            app('activitron')->increment('Site.dpo.request.timeouts');

            $this->throwRequestException($action, $params, $e, 'Network error occurred');
        } catch (ClientRequestException $e) {
            $this->throwRequestException($action, $params, $e, 'Network error occurred');
        } catch (XmlParseException $e) {
            $this->throwRequestException($action, $params, $e, 'Unable to parse the response body as XML');
        }

        // Look at `success` field for indications of failure
        $field = Arr::get($xml->xpath("field[@name='success']"), 0);
        if ($field) {
            if ($this->getXmlAttribute($field, 'value') === 'false') {
                $reason = $this->getXmlAttribute($field, 'reason');
                if ($reason) {
                    $this->errorOccurred("Request failed. Reason: {$reason}");
                } else {
                    $this->errorOccurred('Request failed. No reason was given.');
                }
            }
        }

        // Look for errors
        $elements = $xml->xpath('error');
        if ($elements) {
            foreach ($elements as $element) {
                $this->errorOccurred((string) $element);
            }
        }

        // If there were errors found in the response throw an exception
        if (count($this->lastErrors)) {
            $this->throwRequestException($action, $params, null, $this->lastErrors->implode(PHP_EOL));
        }

        app('activitron')->increment('Site.dpo.request.success');

        // Look for records
        $records = collect($xml->xpath('record'));

        if (count($records) === 1) {
            // For INSERT and UPDATE actions DPO returns a single record
            // containing a single unnamed field with a value representing the affected record ID
            $fields = $records[0]->xpath('field[@name]');
            if (count($fields) === 1) {
                if ($this->getXmlAttribute($fields[0], 'name') === '') {
                    $this->lastResult = (int) $this->getXmlAttribute($fields[0], 'value');

                    return $this->lastResult;
                }
            }
        }

        // Collect the results
        $this->lastResult = collect();

        foreach ($records as $record) {
            $fields = $record->xpath('field[@name]');
            if ($fields) {
                $record = [];
                foreach ($fields as $field) {
                    $record[strtolower($this->getXmlAttribute($field, 'name'))] = $this->getXmlAttribute($field, 'value');
                }
                $this->lastResult->push((object) $record);
            }
        }

        return $this->lastResult;
    }

    protected function throwRequestException(string $action, string $params, ?Throwable $e, string $error): void
    {
        app('activitron')->increment('Site.dpo.request.failure');

        $this->logRequest($action, $params, $e, [
            'duration' => app('activitron')->endTiming('Site.dpo.request.time'),
        ]);

        if ($this->lastResponse) {
            throw new RequestException($this->errorOccurred($error), $this->lastResponse);
        }

        throw new HttpClientException($this->errorOccurred($error));
    }

    protected function logRequest(string $action, string $params, ?Throwable $e = null, array $context = []): void
    {
        $logMethod = $e ? 'error' : 'info';

        Log::channel('donorperfect')->{$logMethod}(
            $action,
            array_filter(array_merge([
                'params' => $params,
                'exception' => optional($e)->getMessage(),
                'request' => $this->redactMessage((string) optional($this->lastResponse)->getRequestDebugString()),
                'response' => $this->redactMessage((string) optional($this->lastResponse)->getResponseDebugString()),
            ], $context))
        );
    }

    protected function redactMessage(string $message): string
    {
        return str_replace([rawurlencode($this->apiKey), rawurlencode($this->pass)], '********', $message);
    }

    /**
     * @param \Exception|string $error
     * @return string
     */
    protected function errorOccurred($error)
    {
        if (is_a($error, Exception::class)) {
            $error = $error->getMessage();
        }

        $this->lastErrors->prepend($error);

        return $error;
    }

    /**
     * Get the value for an XML attribute
     *
     * @param \SimpleXmlElement $element
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getXmlAttribute(SimpleXmlElement $element, $key, $default = null)
    {
        $attributes = $element->attributes();

        return isset($attributes[$key]) ? (string) $attributes[$key] : $default;
    }

    /**
     * Escapes special characters in a string for use in an SQL statement
     *
     * @param string|float|int $string
     * @return string
     */
    public function escape($string)
    {
        if (trim($string) === '') {
            return '';
        }

        if (is_numeric($string)) {
            return $string;
        }

        // Strip non-displayable characters
        $nonDisplayables = [
            '/%0[0-8bcef]/',  // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',   // url encoded 16-31
            '/[\x00-\x08]/',  // 00-08
            '/\x0b/',         // 11
            '/\x0c/',         // 12
            '/[\x0e-\x1f]/',  // 14-31
        ];

        foreach ($nonDisplayables as $pattern) {
            $string = preg_replace($pattern, '', $string);
        }

        // Escape single quotes
        return str_replace("'", "''", $string);
    }
}
