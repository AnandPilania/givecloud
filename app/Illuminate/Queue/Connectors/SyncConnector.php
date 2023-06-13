<?php

namespace Ds\Illuminate\Queue\Connectors;

use Ds\Illuminate\Queue\SyncQueue;
use Illuminate\Queue\Connectors\SyncConnector as Connector;

class SyncConnector extends Connector
{
    /**
     * Establish a queue connection.
     *
     * @param array $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new SyncQueue;
    }
}
