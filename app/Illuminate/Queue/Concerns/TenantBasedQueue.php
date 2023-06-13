<?php

namespace Ds\Illuminate\Queue\Concerns;

trait TenantBasedQueue
{
    /**
     * Get the queue or return the default.
     *
     * @param string|null $queue
     * @return string
     */
    public function getQueue($queue)
    {
        $prefix = 'site:' . sys_get('ds_account_name') . ':';

        if (substr((string) $queue, 0, strlen($prefix)) === $prefix) {
            $queue = substr($queue, strlen($prefix));
        }

        return $prefix . ($queue ?: $this->default);
    }
}
