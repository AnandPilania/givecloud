<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase as TestingRefreshDatabase;

trait RefreshDatabase
{
    use TestingRefreshDatabase {
        TestingRefreshDatabase::refreshDatabase as performDatabaseRefresh;
    }

    protected bool $refreshDatabase = true;

    public function refreshDatabase(): void
    {
        if ($this->refreshDatabase) {
            $this->performDatabaseRefresh();
        }
    }
}
