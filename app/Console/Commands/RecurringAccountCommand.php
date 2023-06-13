<?php

namespace Ds\Console\Commands;

use Ds\Events\RecurringPaymentWasCompleted;
use Ds\Models\Member as Account;
use Ds\Models\Payment;
use Ds\Models\PaymentMethod;
use Ds\Models\RecurringBatch;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Transaction;
use Ds\Services\PaymentService;
use Ds\Services\TransactionService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class RecurringAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:account {--dry-run} {--batch-id=} {account_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge recurring payment profiles for a supporter in batches.';

    /** @var \Ds\Models\RecurringBatch|null */
    protected $batch;

    /** @var bool */
    protected $dryRun;

    /**
     * Execute the console command.
     */
    public function handle(PaymentService $paymentService, TransactionService $transactionService)
    {
        $account = Account::find($this->argument('account_id'));

        if (empty($account)) {
            $this->error('Supporter not found.');

            return 1;
        }

        $this->batch = RecurringBatch::find($this->option('batch-id'));
        $this->dryRun = (bool) $this->option('dry-run');

        $chargeableRppBatches = $this->getChargeableRppBatches($account);

        if ($chargeableRppBatches->isEmpty()) {
            $this->error('No chargeable rpps.');

            return 0;
        }

        foreach ($chargeableRppBatches as $currencyCode => $paymentMethodBatches) {
            foreach ($paymentMethodBatches as $paymentMethodId => $platformFeeTypeBatches) {
                foreach ($platformFeeTypeBatches as $platformFeeType => $chargeableRpps) {
                    try {
                        $this->processChargeableRppBatch(
                            $account,
                            PaymentMethod::findOrFail($paymentMethodId),
                            $currencyCode,
                            $chargeableRpps,
                            $paymentService,
                            $transactionService,
                            $platformFeeType ?: null,
                        );
                    } catch (Throwable $e) {
                        $this->notifyException($e);
                    }
                }
            }
        }
    }

    /**
     * Get chargeable RPPs for an account grouped into batches based on
     * both currency code and payment method.
     */
    private function getChargeableRppBatches(Account $account): Collection
    {
        return $account->chargeableRpps
            ->groupBy('currency_code')
            ->map(function ($chargeableRpps) use ($account) {
                return $chargeableRpps->groupBy(function ($rpp) use ($account) {
                    return optional($rpp->paymentMethod)->getKey() ?? optional($account->defaultPaymentMethod)->getKey() ?? null;
                })->reject(function ($chargeableRpps, $paymentMethodId) {
                    return empty($paymentMethodId);
                })->map(function ($chargeableRpps) {
                    return $chargeableRpps->groupBy('platform_fee_type');
                });
            });
    }

    /**
     * Create a single payment covering multiple RPPs.
     */
    private function processChargeableRppBatch(
        Account $account,
        PaymentMethod $paymentMethod,
        string $currencyCode,
        Collection $chargeableRpps,
        PaymentService $paymentService,
        TransactionService $transactionService,
        ?string $platformFeeType
    ): void {
        if ($this->dryRun) {
            $this->printChargeableRppSummary($account, $paymentMethod, $currencyCode, $chargeableRpps);

            return;
        }

        $this->info(sprintf(
            'charging (%d) rpps in [%s] using payment method (ID: %s)',
            count($chargeableRpps),
            $currencyCode,
            $paymentMethod->getKey()
        ));

        $payment = $this->chargeRpps($paymentService, $paymentMethod, $currencyCode, $chargeableRpps, $platformFeeType);

        foreach ($chargeableRpps as $rpp) {
            try {
                $this->processTransaction($transactionService, $rpp, $payment);
            } catch (Throwable $e) {
                $this->notifyException($e);
            }
        }
    }

    /**
     * Print a summary of the chargeable RPPs.
     */
    private function printChargeableRppSummary(
        Account $account,
        PaymentMethod $paymentMethod,
        string $currencyCode,
        Collection $chargeableRpps
    ): void {
        $this->comment(sprintf(
            '| %6d  %32s  %18s  %5s  %2d  %11s  %s',
            $account->getKey(),
            $account->display_name,
            $paymentMethod->account_type,
            $paymentMethod->account_last_four,
            count($chargeableRpps),
            money($chargeableRpps->sum('total_amt'), $currencyCode)->format('$0,0 $$$'),
            $chargeableRpps->pluck('next_billing_date')->map(function ($date) {
                return fromUtcFormat($date, 'Y-M-j');
            })->unique()->implode(', ')
        ));
    }

    /**
     * Create a single payment covering multiple RPPs.
     */
    private function chargeRpps(
        PaymentService $paymentService,
        PaymentMethod $paymentMethod,
        string $currencyCode,
        Collection $chargeableRpps,
        ?string $platformFeeType
    ): Payment {
        $description = sprintf(
            'Payment for %s %s',
            Str::plural('Recurring Payment Profile', count($chargeableRpps)),
            $chargeableRpps->map(function ($rpp) {
                return "#{$rpp->profile_id}";
            })->implode(', ')
        );

        return $paymentService->makePayment(
            $paymentMethod,
            $chargeableRpps->sum('total_amt'),
            $chargeableRpps->sum('dcc_amount'),
            $currencyCode,
            $description,
            $platformFeeType,
        );
    }

    /**
     * Process transaction for an RPP against an existing payment.
     */
    private function processTransaction(
        TransactionService $transactionService,
        RecurringPaymentProfile $rpp,
        Payment $payment
    ): Transaction {
        $transaction = $transactionService->createTransaction($rpp, $payment);

        if ($this->batch) {
            $this->batch->transactions()->save($transaction);
        }

        if ($transaction->is_payment_accepted) {
            member_notify_recurring_payment($rpp, 'success');

            event(new RecurringPaymentWasCompleted($rpp, $transaction));
        } else {
            member_notify_recurring_payment($rpp, 'failure');
        }

        return $transaction;
    }

    /**
     * Output and notify the exception.
     */
    private function notifyException(Throwable $e): void
    {
        $this->error($e);

        notifyException($e);
    }
}
