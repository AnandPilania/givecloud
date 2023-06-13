<?php

namespace Ds\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\SalesforceTokenStorage;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\RedirectResponse;
use Omniphx\Forrest\Exceptions\MissingTokenException;
use Omniphx\Forrest\Providers\Laravel\Facades\Forrest;

class SalesforceClientService
{
    public function authenticate(): RedirectResponse
    {
        return Forrest::authenticate();
    }

    public function authenticated(): void
    {
        sys_set('salesforce_enabled', true);
    }

    public function callback(): array
    {
        $state = Forrest::callback();

        $this->authenticated();

        return $state;
    }

    public function isEnabled(): bool
    {
        return $this->isInstalled()
            && sys_get('bool:salesforce_enabled', false);
    }

    public function isInstalled(): bool
    {
        return sys_get('bool:feature_salesforce', false);
    }

    public function hasToken(): bool
    {
        try {
            return $this->isEnabled() && $this->token();
        } catch (MissingTokenException $e) {
            return false;
        }
    }

    public function revoke(): bool
    {
        try {
            Forrest::revoke();
        } catch (RequestException $e) {
            parse_str((string) $e->getResponse()->getBody(), $response);

            if (! in_array(data_get($response, 'error'), [
                'unsupported_token_type',
                'invalid_token',
            ])) {
                throw $e;
            }
        }

        app(SalesforceTokenStorage::class)->forget('token');

        sys_set('salesforce_enabled', false);

        return true;
    }

    public function token(): array
    {
        if ($token = app(SalesforceTokenStorage::class)->get('token')) {
            return decrypt($token);
        }

        throw new MissingTokenException('No token available');
    }

    public function test(): bool
    {
        try {
            Forrest::identity();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getExceptionMessage(\Exception $exception): string
    {
        if (method_exists($exception, 'hasResponse') && $exception->hasResponse()) {
            return ucfirst(data_get(json_decode($exception->getResponse()->getBody()), 'error_description', 'an error occured'));
        }

        return $exception->getMessage();
    }
}
