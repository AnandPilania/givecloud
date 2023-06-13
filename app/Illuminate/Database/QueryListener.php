<?php

namespace Ds\Illuminate\Database;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class QueryListener
{
    /** @var bool */
    protected $enabled = false;

    /** @var bool */
    protected $listening = false;

    /** @var array */
    protected $queries = [];

    /** @var string */
    protected $logPath;

    /**
     * Start listening immediately upon initialization.
     */
    public function __construct()
    {
        $this->listen();
    }

    /**
     * Enable the general log for any queries perform within
     * a closure or toggle the general log.
     *
     * @param \Closure|bool $action
     * @return mixed
     */
    public function listen($action = true)
    {
        if ($action) {
            if (! $this->listening) {
                Event::listen(QueryExecuted::class, [$this, 'registerQuery']);
                $this->listening = true;
            }
            $this->enabled = true;
        } elseif ($action === false) {
            $this->enabled = false;
        }

        if (is_callable($action)) {
            try {
                return $action();
            } finally {
                $this->enabled = false;
            }
        }
    }

    /**
     * Log the query into the internal store.
     *
     * @param \Illuminate\Database\Events\QueryExecuted $event
     */
    public function registerQuery(QueryExecuted $event)
    {
        if ($this->enabled) {
            $query = [
                'query' => $this->createRunnableQuery($event->sql, $event->bindings, $event->connection),
                'time' => $event->time,
            ];

            if ($this->logPath) {
                file_put_contents(
                    $this->logPath,
                    sprintf("Query: %s\nDuration: %s\n\n", $query['query'], $query['time']),
                    FILE_APPEND
                );
            } else {
                $this->queries[] = $query;
            }
        }
    }

    /**
     * Takes a query, an array of bindings and the connection as arguments, returns runnable
     * query with upper-cased keywords.
     *
     * @param string $query
     * @param array $bindings
     * @param \Illuminate\Database\ConnectionInterface $connection
     * @return string
     *
     * @see: https://github.com/itsgoingd/clockwork/blob/master/Clockwork/DataSource/EloquentDataSource.php#L160
     */
    protected function createRunnableQuery($query, array $bindings, ConnectionInterface $connection)
    {
        // add bindings to query
        $bindings = $connection->prepareBindings($bindings);

        foreach ($bindings as $binding) {
            $binding = $connection->getPdo()->quote($binding);

            // convert binary bindings to hexadecimal representation
            if (! preg_match('//u', $binding)) {
                $binding = '0x' . bin2hex($binding);
            }

            // escape backslashes in the binding (preg_replace requires to do so)
            $binding = str_replace('\\', '\\\\', $binding);

            $query = preg_replace('/\?/', $binding, $query, 1);
        }

        // highlight keywords
        $keywords = [
            'select', 'insert', 'update', 'delete', 'where', 'from', 'limit', 'is', 'null', 'having', 'group by',
            'order by', 'asc', 'desc',
        ];
        $regexp = '/\b' . implode('\b|\b', $keywords) . '\b/i';

        return preg_replace_callback($regexp, function ($match) {
            return strtoupper($match[0]);
        }, $query);
    }

    /**
     * Returns an array of runnable queries and their durations.
     *
     * @return array
     */
    public function get()
    {
        return $this->queries;
    }

    /**
     * Dump runnable queries and their durations.
     */
    public function dump()
    {
        foreach ($this->get() as $query) {
            dump($query); // phpcs:ignore
        }

        $this->queries = [];
    }

    /**
     * Die and dump runnable queries and their durations.
     */
    public function dd()
    {
        $this->dump();
        exit(1);
    }

    /**
     * Log queries for the request in realtime so that queries
     * will still be captured in the event of a fatal error or timeout.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $path
     * @return bool
     */
    public function logQueries(Request $request, string $path): bool
    {
        if (! touch($path)) {
            return false;
        }

        $this->logPath = $path;

        $message = sprintf(
            "-----%s--\n%s /%s\nHost: %s\nDate: %s\n\n",
            spl_object_hash($request),
            $request->method(),
            ltrim($request->path(), '/'),
            $request->getHost(),
            fromUtcFormat('now', 'r')
        );

        file_put_contents($this->logPath, $message, FILE_APPEND);

        $this->listen();

        return true;
    }
}
