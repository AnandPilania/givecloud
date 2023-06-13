<?php

namespace Ds\Services\Order;

use Ds\Models\Order;
use Illuminate\Support\Facades\Validator;
use Swift_Validate;

class OrderEmailPreferencesService
{
    public function hasValidEmail(Order $order): bool
    {
        $validator = Validator::make(
            ['email' => trim($order->billingemail)],
            ['email' => 'required|email']
        );

        return $validator->passes();
    }

    public function shouldSendEmail(Order $order): bool
    {
        if (Swift_Validate::email($order->billingemail) === false) {
            return false;
        }

        return (bool) $order->send_confirmation_emails;
    }

    public function shouldSendLegacyEmail(Order $order): bool
    {
        if (! $this->shouldSendEmail($order)) {
            return false;
        }

        return ! $this->shouldSendModernEmail($order);
    }

    public function shouldSendModernEmail(Order $order): bool
    {
        if (! $this->shouldSendEmail($order)) {
            return false;
        }

        return $order->isForFundraisingForm();
    }
}
