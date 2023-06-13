<?php

namespace Ds\Common\Illuminate\Queue;

use Ds\Illuminate\Queue\CallQueuedHandler as BaseCallQueuedHandler;

/**
 * Required for transition period so that currently queued jobs
 * don't run into errors when attempting to deserialize this class
 * which has been moved.
 *
 * Once all pre-deploy jobs have left the queue this class can
 * be deleted once and for all.
 */
class CallQueuedHandler extends BaseCallQueuedHandler
{
    // nothing
}
