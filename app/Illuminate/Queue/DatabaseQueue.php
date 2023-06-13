<?php

namespace Ds\Illuminate\Queue;

use Illuminate\Queue\DatabaseQueue as Queue;

class DatabaseQueue extends Queue
{
    use Concerns\HasUtf8Payload;
    use Concerns\TenantBasedQueue;
}
