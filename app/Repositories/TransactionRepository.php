<?php

namespace Ds\Repositories;

use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Transaction;
use Ds\Services\TransactionService;
use Throwable;

class TransactionRepository
{
    public function getRandomSucceededTransaction(int $limitToLatest = 20): ?Transaction
    {
        $transactions = Transaction::query()
            ->succeeded()
            ->orderByDesc('id')
            ->take($limitToLatest)
            ->get();

        if ($transactions->isEmpty()) {
            return null;
        }

        return $transactions->random()->first();
    }

    /**
     * Process the transaction and update the profile based
     * on the tranaction status.
     *
     * @param \Ds\Models\RecurringPaymentProfile $profile
     * @param \Ds\Models\Transaction $transaction
     * @param bool $createPayment
     */
    public function handleTransaction(RecurringPaymentProfile $profile, Transaction $transaction, $createPayment = true)
    {
        $profile->lockProfile();

        try {
            $res = $transaction->process();
        } catch (Throwable $e) {
            $transaction->transaction_status = 'Error';
            $transaction->transactionLog($e);
        }

        if ($createPayment) {
            app(TransactionService::class)->createPayment($transaction, $res);
        }

        // Trigger Observers with new payment.
        $transaction->refresh()->save();

        if ($transaction->is_payment_accepted) {
            $profile->last_payment_date = $transaction->order_time->copy();
            $profile->last_payment_amt = $transaction->amt;
            $profile->aggregate_amount += $transaction->amt;
            $profile->num_cycles_completed++;
            $profile->next_billing_date = $profile->next_possible_billing_date->copy();
            $profile->next_attempt_date = null;
            $profile->failed_payment_count = 0;
            if ($profile->num_cycles_remaining) {
                $profile->num_cycles_remaining--;
            }

            if ($profile->num_cycles_remaining === 0) {
                $profile->status = 'Expired';
            }

            $profile->unlockProfile();

            return;
        }

        // Add the transaction amount to the outstanding balance on the
        // first occurrance of a failed payment
        if ($profile->failed_payment_count === 0) {
            $profile->outstanding_balance += $transaction->amt;
        }

        $profile->failed_payment_count++;

        // Prevent the profiles from being suspended if the
        // reason the transaction failed was because of a duplicate check
        if (preg_match('/^Duplicate (order|contribution|transaction)/i', $transaction->reason_code)) {
            $profile->next_attempt_date = toUtc('today');
            $profile->unlockProfile();

            return;
        }

        $profile->outstanding_balance += $profile->nsf_fee;

        // Suspend profile once the maximum number of attempts has been made
        if ($profile->failed_payment_count >= $profile->max_failed_payments) {
            $profile->status = RecurringPaymentProfileStatus::SUSPENDED;
            $profile->next_attempt_date = null;
            $profile->unlockProfile();

            return;
        }

        $profile->next_attempt_date = toUtc('+' . (int) sys_get('rpp_retry_interval') . ' days');
        $profile->unlockProfile();
    }
}
