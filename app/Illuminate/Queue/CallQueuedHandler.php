<?php

namespace Ds\Illuminate\Queue;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\CallQueuedHandler as BaseCallQueuedHandler;

class CallQueuedHandler extends BaseCallQueuedHandler
{
    /**
     * Handle the queued job.
     *
     * @param \Illuminate\Contracts\Queue\Job $job
     * @param array $data
     * @return void
     */
    public function call(Job $job, array $data)
    {
        $data['command'] = utf8_decode($data['command']);

        parent::call($job, $data);
    }

    /**
     * Call the failed method on the job instance.
     *
     * The exception that caused the failure will be passed.
     *
     * @param array $data
     * @param \Throwable|null $e
     * @param string $uuid
     * @return void
     */
    public function failed(array $data, $e, string $uuid)
    {
        $data['command'] = utf8_decode($data['command']);

        parent::failed($data, $e, $uuid);
    }
}
