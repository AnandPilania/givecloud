<?php

namespace Ds\Common\DonorPerfect;

use BadMethodCallException;
use Closure;
use DateTimeInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\Grammar as QueryGrammar;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use Illuminate\Database\Query\Processors\Processor as QueryProcessor;
use Illuminate\Support\Arr;
use Throwable;

class Connection implements ConnectionInterface
{
    /**
     * The active DPO connection.
     *
     * @var \Ds\Common\DonorPerfect\Client
     */
    protected $dpo;

    /**
     * The query grammar implementation.
     *
     * @var \Illuminate\Database\Query\Grammars\SqlServerGrammar
     */
    protected $queryGrammar;

    /**
     * The query post processor implementation.
     *
     * @var \Ds\Common\DonorPerfect\Processor
     */
    protected $postProcessor;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher|null
     */
    protected $events;

    /**
     * The number of active transactions.
     *
     * @var int
     */
    protected $transactions = 0;

    /**
     * All of the queries run against the connection.
     *
     * @var array
     */
    protected $queryLog = [];

    /**
     * Indicates whether queries are being logged.
     *
     * @var bool
     */
    protected $loggingQueries = false;

    /**
     * Indicates if the connection is in a "dry run".
     *
     * @var bool
     */
    protected $pretending = false;

    /**
     * The database connection configuration options.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Create a new database connection instance.
     *
     * @param Client $dpo
     * @param array $config
     * @return void
     */
    public function __construct(Client $dpo, array $config = [])
    {
        $this->dpo = $dpo;

        $this->config = $config;

        // We need to initialize a query grammar and the query post processors
        // which are both very important parts of the database abstractions
        // so we initialize these to their default values while starting.
        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();
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
     * Set the query grammar to the default implementation.
     *
     * @return void
     */
    public function useDefaultQueryGrammar()
    {
        $this->queryGrammar = $this->getDefaultQueryGrammar();
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \Illuminate\Database\Query\Grammars\SqlServerGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return new SqlServerGrammar;
    }

    /**
     * Set the query post processor to the default implementation.
     *
     * @return void
     */
    public function useDefaultPostProcessor()
    {
        $this->postProcessor = $this->getDefaultPostProcessor();
    }

    /**
     * Get the default post processor instance.
     *
     * @return \Ds\Common\DonorPerfect\Processor
     */
    protected function getDefaultPostProcessor()
    {
        return new Processor;
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
        return $this->query()->from($table, $as);
    }

    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return new Builder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

    /**
     * Get a new raw query expression.
     *
     * @param mixed $value
     * @return \Illuminate\Database\Query\Expression
     */
    public function raw($value)
    {
        return new Expression($value);
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
        return $this->run($query, $bindings, function ($me, $query, $bindings) use ($useReadPdo) {
            if ($me->pretending()) {
                return [];
            }

            // For select statements, we'll simply execute the query and return an array
            // of the database result set. Each element in the array will be a single
            // row from the database table, and will either be an array or objects.
            $query = $me->emulatePrepare($query, $bindings);

            return $this->dpo->request($query);
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
        throw new BadMethodCallException('Unsupported');
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
        return $this->statement($query, $bindings);
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
        return $this->affectingStatement($query, $bindings);
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
        return $this->affectingStatement($query, $bindings);
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
        return $this->run($query, $bindings, function ($me, $query, $bindings) {
            if ($me->pretending()) {
                return true;
            }

            $query = $me->emulatePrepare($query, $bindings);

            return (bool) $me->dpo->request($query);
        });
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
        return $this->run($query, $bindings, function ($me, $query, $bindings) {
            if ($me->pretending()) {
                return 0;
            }

            // For update or delete statements, we want to get the number of rows affected
            // by the statement and return that back to the developer. We'll first need
            // to execute the statement and then we'll use PDO to fetch the affected.
            $query = $me->emulatePrepare($query, $bindings);

            return $me->dpo->request($query);
        });
    }

    /**
     * Run a raw, unprepared query against the DPO connection.
     *
     * @param string $query
     * @return bool
     */
    public function unprepared($query)
    {
        return $this->run($query, [], function ($me, $query) {
            if ($me->pretending()) {
                return true;
            }

            return (bool) $me->dpo->request($query);
        });
    }

    /**
     * Run a request against the DPO connection.
     *
     * @param string $action
     * @param string|array $params
     * @return \Illuminate\Support\Collection|array|int
     *
     * @throws \Ds\Common\DonorPerfect\RequestException
     */
    public function request($action, $params = [])
    {
        return $this->run($action, $params, function ($me, $action, $params) {
            if ($me->pretending()) {
                return true;
            }

            $result = $me->dpo->request($action, $params);

            // In order to maintain compatibility with the original `dpo_request()`
            // function call we'll return an array instead of a collection
            if (is_a($result, 'Illuminate\Support\Collection')) {
                return $result->toArray();
            }

            return $result;
        });
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param array $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            // We need to transform all instances of DateTimeInterface into the actual
            // date string. Each query grammar maintains its own date string format
            // so we'll just ask the grammar for the format to get from the date.
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif ($value === false) {
                $bindings[$key] = 0;
            }
        }

        return $bindings;
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param \Closure $callback
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transaction(Closure $callback, $attempts = 1)
    {
        $this->beginTransaction();

        // We'll simply execute the given callback within a try / catch block
        // and if we catch any exception we can rollback the transaction
        // so that none of the changes are persisted to the database.
        try {
            $result = $callback($this);

            $this->commit();
        }

        // If we catch an exception, we will roll back so nothing gets messed
        // up in the database. Then we'll re-throw the exception so it can
        // be handled how the developer sees fit for their applications.
        catch (Throwable $e) {
            $this->rollBack();

            throw $e;
        }

        return $result;
    }

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->transactions++;

        $this->fireConnectionEvent('beganTransaction');
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        $this->transactions = max(0, $this->transactions - 1);

        $this->fireConnectionEvent('committed');
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        $this->transactions = max(0, $this->transactions - 1);

        $this->fireConnectionEvent('rollingBack');
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel()
    {
        return $this->transactions;
    }

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param \Closure $callback
     * @return array
     */
    public function pretend(Closure $callback)
    {
        $loggingQueries = $this->loggingQueries;

        $this->enableQueryLog();

        $this->pretending = true;

        $this->queryLog = [];

        // Basically to make the database connection "pretend", we will just return
        // the default values for all the query methods, then we will return an
        // array of queries that were "executed" within the Closure callback.
        $callback($this);

        $this->pretending = false;

        $this->loggingQueries = $loggingQueries;

        return $this->queryLog;
    }

    /**
     * Run a SQL statement and log its execution context.
     *
     * @param string $query
     * @param array $bindings
     * @param \Closure $callback
     * @return mixed
     *
     * @throws \Illuminate\Database\QueryException
     */
    protected function run($query, $bindings, Closure $callback)
    {
        $start = microtime(true);

        $result = $this->runQueryCallback($query, $bindings, $callback);

        // Once we have run the query we will calculate the time that it took to run and
        // then log the query, bindings, and execution time so we will report them on
        // the event that the developer needs them. We'll log time in milliseconds.
        $time = $this->getElapsedTime($start);

        $this->logQuery($query, $bindings, $time);

        return $result;
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
        // To execute the statement, we'll simply call the callback, which will actually
        // run the SQL against the wpdb connection. Then we can calculate the time it
        // took to execute and log the query SQL, bindings and time in our memory.
        try {
            $result = $callback($this, $query, $bindings);
        } catch (RequestException $e) {
            // If an exception occurs when attempting to run a query, we'll format the error
            // message to include the bindings with SQL, which will make this exception a
            // lot more helpful to the developer instead of just the database's errors.
            if (substr_count($e->getMessage(), '[Microsoft][ODBC SQL Server Driver][SQL Server]')) {
                throw new QueryException(
                    $query,
                    $this->prepareBindings((array) $bindings),
                    $e
                );
            }

            // If a request exception occurs when attempting to run a query,
            // re-throw the request exception, which will make this exception a
            // lot more helpful to the developer instead of just the SQL/database's errors.
            throw $e;
        } catch (Throwable $e) {
            // If an exception occurs when attempting to run a query, we'll format the error
            // message to include the bindings with SQL, which will make this exception a
            // lot more helpful to the developer instead of just the database's errors.
            throw new QueryException(
                $query,
                $this->prepareBindings((array) $bindings),
                $e
            );
        }

        return $result;
    }

    /**
     * Log a query in the connection's query log.
     *
     * @param string $query
     * @param array $bindings
     * @param float|null $time
     * @return void
     */
    public function logQuery($query, $bindings, $time = null)
    {
        if (isset($this->events)) {
            $this->events->dispatch(new QueryExecuted(
                $query,
                (array) $bindings,
                $time,
                $this
            ));
        }

        if ($this->loggingQueries) {
            $this->queryLog[] = compact('query', 'bindings', 'time');
        }
    }

    /**
     * Register a database query listener with the connection.
     *
     * @param \Closure $callback
     * @return void
     */
    public function listen(Closure $callback)
    {
        if (isset($this->events)) {
            $this->events->listen(QueryExecuted::class, $callback);
        }
    }

    /**
     * Fire an event for this connection.
     *
     * @param string $event
     * @return void
     */
    protected function fireConnectionEvent($event)
    {
        if (! isset($this->events)) {
            return;
        }

        switch ($event) {
            case 'beganTransaction':
                return $this->events->dispatch(new TransactionBeginning($this));
            case 'committed':
                return $this->events->dispatch(new TransactionCommitted($this));
            case 'rollingBack':
                return $this->events->dispatch(new TransactionRolledBack($this));
        }
    }

    /**
     * Get the elapsed time since a given starting point.
     *
     * @param float $start
     * @return float
     */
    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public function getPdo()
    {
        // return dummy in-memory PDO instance to prevent
        // errors in 3rd-party packages where possible
        return new \PDO('sqlite::memory:');
    }

    /**
     * Get the current DPO connection.
     *
     * @return \Ds\Common\DonorPerfect\Client
     */
    public function getClient()
    {
        return $this->dpo;
    }

    /**
     * Get an option from the configuration options.
     *
     * @param string $option
     * @return mixed
     */
    public function getConfig($option)
    {
        return Arr::get($this->config, $option);
    }

    /**
     * Get the query grammar used by the connection.
     *
     * @return \Illuminate\Database\Query\Grammars\Grammar
     */
    public function getQueryGrammar()
    {
        return $this->queryGrammar;
    }

    /**
     * Set the query grammar used by the connection.
     *
     * @param \Illuminate\Database\Query\Grammars\Grammar $grammar
     * @return void
     */
    public function setQueryGrammar(QueryGrammar $grammar)
    {
        $this->queryGrammar = $grammar;
    }

    /**
     * Get the query post processor used by the connection.
     *
     * @return \Illuminate\Database\Query\Processors\Processor
     */
    public function getPostProcessor()
    {
        return $this->postProcessor;
    }

    /**
     * Set the query post processor used by the connection.
     *
     * @param \Illuminate\Database\Query\Processors\Processor $processor
     * @return void
     */
    public function setPostProcessor(QueryProcessor $processor)
    {
        $this->postProcessor = $processor;
    }

    /**
     * Get the event dispatcher used by the connection.
     *
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->events;
    }

    /**
     * Set the event dispatcher instance on the connection.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     * @return void
     */
    public function setEventDispatcher(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * Determine if the connection in a "dry run".
     *
     * @return bool
     */
    public function pretending()
    {
        return $this->pretending === true;
    }

    /**
     * Get the connection query log.
     *
     * @return array
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * Clear the query log.
     *
     * @return void
     */
    public function flushQueryLog()
    {
        $this->queryLog = [];
    }

    /**
     * Enable the query log on the connection.
     *
     * @return void
     */
    public function enableQueryLog()
    {
        $this->loggingQueries = true;
    }

    /**
     * Disable the query log on the connection.
     *
     * @return void
     */
    public function disableQueryLog()
    {
        $this->loggingQueries = false;
    }

    /**
     * Determine whether we're logging queries.
     *
     * @return bool
     */
    public function logging()
    {
        return $this->loggingQueries;
    }

    /**
     * Get the name of the connected database.
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return '(none)';
    }

    /**
     * Prepares a SQL query for safe execution. Uses sprintf()-like syntax.
     *
     * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/wp-db.php#L1305
     *
     * @param string $query
     * @param array $bindings
     * @param bool $update
     * @return string
     */
    protected function emulatePrepare($query, array $bindings = [], $update = false)
    {
        // Replace percentage signs with a double percentage sign so
        // to ensure that vsprintf treats them as literal characters
        $query = str_replace('%', '%%', $query);

        // Replace question mark placeholders with the vsprintf %s placeholder
        // and in without replacing question marks that have been quoted with a backtick
        $query = preg_replace('#\?(?=(?:[^`]*`[^`]*`)*[^`]*\Z)#', '%s', $query);

        if (count($bindings) === 0) {
            return $query;
        }

        $query = str_replace("'%s'", '%s', $query); // in case someone mistakenly already singlequoted it
        $query = str_replace('"%s"', '%s', $query); // doublequote unquoting
        $query = preg_replace('|(?<!%)%f|', '%F', $query); // Force floats to be locale unaware

        $bindings = $this->prepareBindings($bindings);

        // This is where we differ from wpdb::prepare() instead wrapping the placeholders
        // with quotes we wrap the bindings with quotes. This is require to add support for NULLs.
        foreach ($bindings as &$binding) {
            if ($binding === null) {
                $binding = 'NULL';
            } else {
                $binding = "'" . $this->dpo->escape($binding) . "'";
            }
        }

        return @vsprintf($query, $bindings);
    }
}
