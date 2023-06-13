<?php

namespace Ds\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\Responses\TransactionResponse;
use Omnipay\NMI\Message\ThreeStepRedirectResponse;
use Omnipay\NMI\ThreeStepRedirectGateway;

class CaymanGateway extends NMIGateway
{
    /** @var string */
    protected $redirectApiEndpoint = 'https://api.caymangateway.com/apiv2/three-step';

    /** @var string */
    protected $testRedirectApiEndpoint = 'https://apidev.caymangateway.com/apiv2/three-step';

    /** @var string */
    protected $queryApiEndpoint = null;

    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'caymangateway';
    }

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'Cayman Gateway';
    }

    public function getWebsiteUrl(): ?string
    {
        return 'https://caymangateway.com';
    }

    /**
     * Get the redirect api client.
     *
     * @return \Omnipay\NMI\ThreeStepRedirectGateway
     */
    protected function getRedirectApi()
    {
        if (! $this->redirectApi) {
            $this->redirectApi = app(ThreeStepRedirectGateway::class);
            $this->redirectApi->initialize([
                'api_key' => $this->config('credential3'),
                'endpoint' => $this->config('test_mode')
                    ? $this->testRedirectApiEndpoint
                    : $this->redirectApiEndpoint,
            ]);
        }

        return $this->redirectApi;
    }

    protected function getChargeCaptureTokenResponseToArray(ThreeStepRedirectResponse $res): array
    {
        $data = parent::getChargeCaptureTokenResponseToArray($res);

        // caymangateway is returning the expiry in reverse order, YYMM. however the NMI api
        // returns the expiry as MMYY. so we need to switch it around to be correct.
        $data['cc_exp'] = substr($data['cc_exp'], 2, 2) . substr($data['cc_exp'], 0, 2);

        return $data;
    }

    /**
     * Create a transaction response.
     *
     * @param array $data
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    protected function createTransactionResponse(array $data): TransactionResponse
    {
        $errors = data_get($data, 'gateway_data.errors');

        if ($errors) {
            $errors = explode(',', $errors);
            $errors = implode('. ', $errors);
            $data['response_text'] = trim($data['response_text'], ':.,!') . trim(": $errors.", '.');
        }

        return parent::createTransactionResponse($data);
    }
}
