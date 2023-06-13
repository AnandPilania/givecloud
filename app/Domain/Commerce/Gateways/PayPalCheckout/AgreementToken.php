<?php

namespace Ds\Domain\Commerce\Gateways\PayPalCheckout;

use PayPal\Common\PayPalResourceModel;
use PayPal\Core\PayPalConstants;
use PayPal\Validation\ArgumentValidator;

class AgreementToken extends PayPalResourceModel
{
    /**
     * Get Approval Link
     *
     * @return string|null
     */
    public function getApprovalLink()
    {
        return $this->getLink(PayPalConstants::APPROVAL_URL);
    }

    /**
     * Get token from Approval Link
     *
     * @return string|null
     */
    public function getToken()
    {
        $parameter_name = 'ba_token';
        parse_str(parse_url($this->getApprovalLink(), PHP_URL_QUERY), $query);

        return ! isset($query[$parameter_name]) ? null : $query[$parameter_name];
    }

    /**
     * Creates a billing agreement token. In the JSON request body, include the agreement details. Details include the description, payer, and billing plan.
     *
     * @param \PayPal\Rest\ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param \PayPal\Transport\PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return \PayPal\Api\Payment
     */
    public function create($apiContext = null, $restCall = null)
    {
        $payLoad = $this->toJSON();
        $json = self::executeCall(
            '/v1/billing-agreements/agreement-tokens',
            'POST',
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $this->fromJson($json);

        return $this;
    }

    /**
     * Shows details for an agreement token, by an agreement token ID.
     *
     * @param string $agreementId
     * @param \PayPal\Rest\ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param \PayPal\Transport\PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return static
     */
    public static function get($agreementId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($agreementId, 'tokenId');
        $payLoad = '';
        $json = self::executeCall(
            "/v1/billing-agreements/agreement-tokens/$agreementId",
            'GET',
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new AgreementToken();
        $ret->fromJson($json);

        return $ret;
    }
}
