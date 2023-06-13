<?php

namespace Tests\Fakes;

use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;

class FakeDonorPerfectConnection implements ConnectionInterface
{
    /** @var \Illuminate\Support\Collection */
    public $fakeResults;

    public function __construct(array $fakeResults = [])
    {
        $this->fakeResults = new Collection($fakeResults);
    }

    /**
     * Get the database connection name.
     *
     * @return string|null
     */
    public function getName()
    {
        return 'dpo';
    }

    /**
     * Begin a fluent query against a database table.
     *
     * @param \Closure|\Illuminate\Database\Query\Builder|string $table
     * @param string|null $as
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($table, $as = null)
    {
        return $this;
    }

    /**
     * Get a new raw query expression.
     *
     * @param mixed $value
     * @return \Illuminate\Database\Query\Expression
     */
    public function raw($value)
    {
    }

    /**
     * Run a select statement and return a single result.
     *
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return mixed
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        $records = $this->select($query, $bindings);

        return count($records) > 0 ? reset($records) : null;
    }

    /**
     * Run a select statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return array
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->runQueryCallback($query, $bindings, function ($me, $query, $bindings) use ($useReadPdo) {
            return $this->fakeResults;
        });
    }

    /**
     * Run a select statement against the database and returns a generator.
     *
     * @param string $query
     * @param array $bindings
     * @param bool $useReadPdo
     * @return \Generator
     */
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
    }

    /**
     * Run an insert statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
    }

    /**
     * Run an update statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function update($query, $bindings = [])
    {
    }

    /**
     * Run a delete statement against the database.
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function delete($query, $bindings = [])
    {
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param string $query
     * @param array $bindings
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param string $query
     * @return bool
     */
    public function unprepared($query)
    {
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param array $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param \Closure $callback
     * @param int $attempts
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transaction(Closure $callback, $attempts = 1)
    {
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel()
    {
    }

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param \Closure $callback
     * @return array
     */
    public function pretend(Closure $callback)
    {
        return [];
    }

    /**
     * Get the name of the connected database.
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return 'dpo';
    }

    /**
     * Determine if the connection in a "dry run".
     *
     * @return bool
     */
    public function pretending()
    {
        return true;
    }

    /**
     * Run a SQL statement.
     *
     * @param string $query
     * @param array $bindings
     * @param \Closure $callback
     * @return mixed
     *
     * @throws \Illuminate\Database\QueryException
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        return $callback($this, $query, $bindings);
    }
}
