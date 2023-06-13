<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PayPalController extends Controller
{
    public function verifyConnection()
    {
        try {
            $provider = PaymentProvider::provider('paypalexpress')->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            return response()->json(false);
        }

        return response()->json($provider->gateway->verifyConnection());
    }

    public function verifyReferenceTransactions()
    {
        try {
            $provider = PaymentProvider::provider('paypalexpress')->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            return response()->json(false);
        }

        return response()->json($provider->gateway->verifyReferenceTransactions());
    }
}
