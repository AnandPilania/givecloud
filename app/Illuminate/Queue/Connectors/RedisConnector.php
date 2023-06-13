<?php

namespace Ds\Illuminate\Queue\Connectors;

use Ds\Illuminate\Queue\RedisQueue;
use Illuminate\Queue\Connectors\RedisConnector as Connector;

class RedisConnector extends Connector
{
    /**
     * Establish a queue connection.
     *
     * @param array $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new RedisQueue(
            $this->redis,
            $config['queue'],
            $config['connection'] ?? $this->connection,
            $config['retry_after'] ?? 60,
            $config['block_for'] ?? null
        );
    }
}
