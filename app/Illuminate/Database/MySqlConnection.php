<?php

namespace Ds\Illuminate\Database;

use Closure;
use Ds\Illuminate\Database\Schema\MySqlGrammar as SchemaGrammar;
use Illuminate\Database\MySqlConnection as Connection;
use Illuminate\Support\Arr;

class MySqlConnection extends Connection
{
    /** @var bool */
    protected static $cacheSelects = false;

    /**
     * Get the default schema grammar instance.
     *
     * @return \Illuminate\Database\Schema\Grammars\MySqlGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }

    /**
     * Set the name of the connected database.
     *
     * @param string $database
     * @return $this
     */
    public function setDatabaseName($database)
    {
        Arr::set($this->config, 'database', $database);

        return parent::setDatabaseName($database);
    }

    /**
     * Enable the general log for any queries perform within
     * a closure or toggle the general log.
     *
     * @param \Closure|bool $action
     * @return mixed
     */
    public function generalLog($action = true)
    {
        if ($action) {
            $this->unprepared("SET GLOBAL general_log = 'ON';");
        } elseif ($action === false) {
            $this->unprepared("SET GLOBAL general_log = 'OFF';");
        }

        if (is_callable($action)) {
            try {
                return $action();
            } finally {
                $this->unprepared("SET GLOBAL general_log = 'OFF';");
            }
        }
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
        if (static::$cacheSelects) {
            $key = 'mysqlconnection-selects:' . sha1($query . serialize($bindings));

            if (reqcache()->has($key)) {
                return reqcache()->get($key);
            }
        }

        $result = parent::runQueryCallback($query, $bindings, $callback);

        if (static::$cacheSelects && is_array($result)) {
            reqcache($key, $result);
        }

        return $result;
    }

    /**
     * Execute an SQL query and return the PDO statement.
     *
     * @param string $query
     * @param array $bindings
     * @return \PDOStatement
     */
    public function pdoQuery($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            $statement = $this->getPdo()->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $this->recordsHaveBeenModified();

            $statement->execute();

            return $statement;
        });
    }

    /**
     * Enable/disable caching of selects.
     *
     * @param bool $enableCache
     */
    public static function cacheSelects(bool $enableCache = true)
    {
        if ($enableCache) {
            reqcache()->forget('mysqlconnection-selects:*');
        }

        static::$cacheSelects = $enableCache;
    }

    /**
     * Enable/disable caching of selects.
     *
     * @param bool $enableCache
     */
    public static function runWithSelectCache(Closure $closure)
    {
        static::cacheSelects(true);

        return tap($closure(), function () {
            static::cacheSelects(false);
        });
    }
}
