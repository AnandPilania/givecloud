<?php

namespace Ds\Jobs;

use Ds\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CommitTransactionsToDPO extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $txns = Transaction::with('recurringPaymentProfile.member')->unsynced();

        $total_to_process = $txns->count();
        $failed = [];
        $processed = 0;
        $success = 0;

        try {
            $txns->orderBy('id')->chunk(50, function ($chunk) use (&$processed, &$success, &$failed) {
                foreach ($chunk as $txn) {
                    // if failed sync
                    if (! $txn->commitToDpo()) {
                        $failed[] = 'Txn ' . $txn->transaction_id . ' (' . number_format($txn->amt, 2) . ') for ' . $txn->recurringPaymentProfile->member->display_name . ' failed to send to DP (txn id: ' . $txn->id . '). ' . $txn->last_transaction_log;

                    // success
                    } else {
                        $success++;
                    }

                    // always increment processed
                    $processed++;
                }
            });
        } catch (\Exception $e) {
            $failed[] = 'Fatal error. Stopping job. (' . $e->getMessage() . ')';
        }

        $body = [];
        $body[] = $total_to_process . ' transactions to process';
        $body[] = $processed . ' processed';
        $body[] = $success . ' succeeded';
        $body[] = count($failed) . ' failed:';
        $body = implode('<br>', $body) . '<br>' . implode('<br>', $failed);

        $message = (new \Swift_Message)
            ->setFrom('notifications@givecloud.co', 'Givecloud Bot')
            ->setSubject('Sync All Transactions (' . sys_get('ds_account_name') . '): Complete')
            ->setTo(config('mail.support.address'))
            ->setBody($body, 'text/html');

        send_using_swiftmailer($message);
    }
}
