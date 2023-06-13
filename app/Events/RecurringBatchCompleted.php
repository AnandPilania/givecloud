<?php

namespace Ds\Events;

use Ds\Models\RecurringBatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RecurringBatchCompleted extends Event
{
    use Dispatchable;
    use SerializesModels;

    public RecurringBatch $batch;

    public function __construct(RecurringBatch $batch)
    {
        $this->batch = $batch;
    }
}
