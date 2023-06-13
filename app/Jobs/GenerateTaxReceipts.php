<?php

namespace Ds\Jobs;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Member as Account;
use Ds\Models\Order;
use Ds\Models\TaxReceipt;
use Ds\Models\TaxReceiptTemplate;
use Ds\Models\Transaction;
use Ds\Repositories\AccountRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateTaxReceipts extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /** @var array */
    protected $options;

    /**
     * Create a new job instance.
     *
     * @param array $options
     * @return void
     */
    public function __construct(array $options)
    {
        $validator = app('validator')->make(request()->all(), [
            'receipting_period_from' => 'required|date',
            'receipting_period_to' => 'required|date',
            'min_receiptable' => 'required|min:0',
            'status' => 'required|in:issued,draft',
            'tax_receipt_template_id' => 'required|exists:Ds\Models\TaxReceiptTemplate,id',
            'auto_notify' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }

        $this->options = $options;
    }

    /**
     * Execute the job.
     *
     * @param \Ds\Repositories\AccountRepository $accountRepository
     * @return void
     */
    public function handle(AccountRepository $accountRepository)
    {
        $template = TaxReceiptTemplate::find(
            $this->options['tax_receipt_template_id']
        );

        $accounts = $this->getAccountsWithReceiptableAmounts();

        foreach ($accounts as $account) {
            $this->createReceipt($accountRepository, $account, $template);
        }
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    public function getAccountsWithReceiptableAmounts()
    {
        return Account::query()
            ->select('member.*')
            ->active()
            ->cursor();
    }

    /**
     * Create a tax receipt for the given account.
     *
     * @param \Ds\Repositories\AccountRepository $accountRepository
     * @param \Ds\Models\Member $account
     * @param \Ds\Models\TaxReceiptTemplate $template
     */
    private function createReceipt(
        AccountRepository $accountRepository,
        Account $account,
        TaxReceiptTemplate $template
    ): ?TaxReceipt {
        $data = $accountRepository->getReceiptableAmounts(
            $account,
            $this->options['min_receiptable'],
            $this->options['receipting_period_from'],
            $this->options['receipting_period_to']
        );

        if ($data->isEmpty()) {
            return null;
        }

        $receipt = new TaxReceipt;
        $receipt->status = $this->options['status'];
        $receipt->receipt_type = 'consolidated';
        $receipt->issued_at = fromLocal($this->options['receipt_date'] ?? 'now');
        $receipt->currency_code = (string) currency();
        $receipt->setAccount($account);
        $receipt->setTemplate($template);
        $receipt->save();

        $data = $data->groupBy('type')
            ->map(function ($data) {
                return $data->pluck('id');
            });

        foreach ($data as $type => $ids) {
            if ($type === 'order') {
                foreach (Order::withSpam()->whereIn('id', $ids)->cursor() as $order) {
                    $receipt->attachOrder($order);
                }
            } elseif ($type === 'transaction') {
                foreach (Transaction::whereIn('id', $ids)->cursor() as $transaction) {
                    $receipt->attachTransaction($transaction);
                }
            }
        }

        if ($this->options['auto_notify']) {
            try {
                $receipt->notify();
            } catch (Throwable $e) {
                //
            }
        }

        return $receipt;
    }
}
