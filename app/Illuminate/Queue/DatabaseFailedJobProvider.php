<?php

namespace Ds\Illuminate\Queue;

use Illuminate\Queue\Failed\DatabaseFailedJobProvider as FailedJobProvider;

class DatabaseFailedJobProvider extends FailedJobProvider
{
    /**
     * Log a failed job into storage.
     *
     * @param string $connection
     * @param string $queue
     * @param string $payload
     * @param \Exception $exception
     * @return int|null
     */
    public function log($connection, $queue, $payload, $exception)
    {
        $prefix = 'site:' . sys_get('ds_account_name') . ':';

        if (substr((string) $queue, 0, strlen($prefix)) === $prefix) {
            $queue = substr($queue, strlen($prefix));
        }

        return parent::log($connection, $prefix . $queue, $payload, $exception);
    }
}
