<?php

namespace Ds\Domain\Commerce\Gateways;

use Ds\Domain\Commerce\AbstractGateway;
use Ds\Domain\Commerce\Contracts\CaptureTokens;
use Ds\Domain\Commerce\Contracts\Gateway;
use Ds\Domain\Commerce\Contracts\PartialRefunds;
use Ds\Domain\Commerce\Contracts\Refunds;
use Ds\Domain\Commerce\Responses\ErrorResponse;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\Responses\UrlResponse;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;
use Illuminate\Support\Str;

class OfflineGateway extends AbstractGateway implements
    Gateway,
    CaptureTokens,
    Refunds,
    PartialRefunds
{
    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'offline';
    }

    /**
     * Get a display name for the gateway.
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'Offline Payments';
    }

    /**
     * Get url required for creation a capture token.
     *
     * @param \Ds\Models\Order $order
     * @param string|null $returnUrl
     * @param string|null $cancelUrl
     * @return \Ds\Domain\Commerce\Responses\UrlResponse
     */
    public function getCaptureTokenUrl(Order $order, ?string $returnUrl = null, ?string $cancelUrl = null): UrlResponse
    {
        return new ErrorResponse('Not required for creation of capture token');
    }

    /**
     * Charge a capture token.
     *
     * @param \Ds\Models\Order $order
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function chargeCaptureToken(Order $order): TransactionResponse
    {
        return $this->createTransactionResponse([
            'completed' => true,
            'response' => '1',
            'response_text' => 'APPROVED',
            'transaction_id' => Str::random(24),
        ]);
    }

    /**
     * Refund a charge.
     *
     * @param string $transactionId
     * @param float|null $amount
     * @param bool $fullRefund
     * @param \Ds\Models\PaymentMethod|null $paymentMethod
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function refundCharge(string $transactionId, ?float $amount = null, bool $fullRefund = true, ?PaymentMethod $paymentMethod = null): TransactionResponse
    {
        return $this->createTransactionResponse([
            'completed' => true,
            'response' => 'succeeded',
            'response_text' => 'Refund has been approved.',
            'transaction_id' => Str::random(24),
        ]);
    }
}
