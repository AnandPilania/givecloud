<?php

namespace Ds\Http\Controllers;

use Ds\Events\RecurringPaymentWasRefunded;
use Ds\Models\Transaction;
use Throwable;

class TransactionsController extends Controller
{
    /**
     * View a transaction in a modal window.
     */
    public function modal($transaction_id)
    {
        user()->canOrRedirect('transaction');

        $txn = Transaction::find($transaction_id);

        $this->setViewLayout(false);

        return $this->getView('transactions/modal', compact('txn'));
    }

    /**
     * Issue a tax receipt.
     */
    public function issueTaxReceipt($transaction_id)
    {
        user()->canOrRedirect('transaction');

        $txn = Transaction::find($transaction_id);

        try {
            $txn->issueTaxReceipt();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => null,
        ]);
    }

    /**
     * Sync DPO
     */
    public function syncDpo($transaction_id)
    {
        user()->canOrRedirect('admin.dpo');

        $txn = Transaction::find($transaction_id);

        try {
            $txn->commitToDPO();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => null,
        ]);
    }

    /**
     * Refund a transaction.
     */
    public function refund($transaction_id)
    {
        user()->canOrRedirect('transaction.refund');

        $txn = Transaction::find($transaction_id);

        try {
            $txn->refund();
            event(new RecurringPaymentWasRefunded($txn->recurringPaymentProfile, $txn));
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => null,
        ]);
    }

    public function refreshPaymentStatus($id)
    {
        user()->canOrRedirect('transaction.refund');

        $transaction = Transaction::findOrFail($id);

        try {
            optional($transaction->latestPayment)->syncPaymentStatus();
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => null,
        ]);
    }

    public function syncTransactions()
    {
        user()->canOrRedirect('admin.dpo');

        dispatch(new \Ds\Jobs\CommitTransactionsToDPO);

        return response()->json([
            'status' => 'success',
            'message' => null,
        ]);
    }
}
