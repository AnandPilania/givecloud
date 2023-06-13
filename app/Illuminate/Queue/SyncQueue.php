<?php

namespace Ds\Illuminate\Queue;

use Illuminate\Queue\SyncQueue as Queue;

class SyncQueue extends Queue
{
    use Concerns\HasUtf8Payload;
}
