<?php

namespace Ds\Common\CDN;

use Google\Cloud\Storage\StorageClient as GoogleStorageClient;

class StorageClient extends GoogleStorageClient
{
    /**
     * Get connection.
     *
     * @return \Google\Cloud\Storage\Connection\Rest
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
