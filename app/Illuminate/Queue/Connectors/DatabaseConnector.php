<?php

namespace Ds\Illuminate\Queue\Connectors;

use Ds\Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Connectors\DatabaseConnector as Connector;

class DatabaseConnector extends Connector
{
    /**
     * Establish a queue connection.
     *
     * @param array $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new DatabaseQueue(
            $this->connections->connection($config['connection'] ?? null),
            $config['table'],
            $config['queue'],
            $config['retry_after'] ?? 60
        );
    }
}
