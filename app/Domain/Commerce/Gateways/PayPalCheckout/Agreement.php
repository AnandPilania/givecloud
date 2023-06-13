<?php

namespace Ds\Domain\Commerce\Gateways\PayPalCheckout;

use PayPal\Common\PayPalResourceModel;
use PayPal\Validation\ArgumentValidator;

class Agreement extends PayPalResourceModel
{
    /**
     * Creates an agreement by using an agreement token.
     *
     * @param \PayPal\Rest\ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param \PayPal\Transport\PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return \PayPal\Api\Payment
     */
    public function create($apiContext = null, $restCall = null)
    {
        $payLoad = $this->toJSON();
        $json = self::executeCall(
            '/v1/billing-agreements/agreements',
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
     * Shows details for a billing agreement, by ID.
     *
     * @param string $agreementId
     * @param \PayPal\Rest\ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param \PayPal\Transport\PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return static
     */
    public static function get($agreementId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($agreementId, 'agreementId');
        $payLoad = '';
        $json = self::executeCall(
            "/v1/billing-agreements/agreements/$agreementId",
            'GET',
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new Agreement();
        $ret->fromJson($json);

        return $ret;
    }
}
