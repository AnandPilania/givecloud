<?php

namespace Ds\Illuminate\Queue;

use Illuminate\Queue\RedisQueue as Queue;

class RedisQueue extends Queue
{
    use Concerns\HasUtf8Payload;
    use Concerns\TenantBasedQueue;
}
