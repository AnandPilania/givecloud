<?php

namespace Ds\Illuminate\Database;

use Ds\Domain\Shared\Exceptions\MessageException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\MySqlConnection;
use Illuminate\Support\Facades\DB;
use mysqli;

class MySqlSnapshot
{
    /** @var \Illuminate\Database\MySqlConnection */
    private $connection;

    /** @var \mysqli */
    private $mysqli;

    /** @var string */
    private $databaseName;

    /** @var string */
    private $name;

    /** @var string */
    private $filename;

    /**
     * @param \Illuminate\Database\MySqlConnection $connection
     * @param string $name
     * @param string $databaseName
     */
    public function __construct(MySqlConnection $connection, string $name, string $databaseName = null)
    {
        if (! sys_get()->inTestingEnvironment()) {
            throw new MessageException('Snapshots can only be loaded in a testing environment');
        }

        $this->connection = $connection;
        $this->databaseName = $databaseName ?? config('database.connections.testing.database');

        $this->setName($name);
    }

    /**
     * Loads snapshot with a given name into the database.
     *
     * @param string $name
     * @param string $databaseName
     * @return \Ds\Illuminate\Database\MySqlSnapshot
     */
    public static function load(string $name, string $databaseName = null): MySqlSnapshot
    {
        return (new static(DB::connection(), $name, $databaseName))->loadIntoDatabase();
    }

    /**
     * Loads snapshot with a given name into the database.
     *
     * @param string $name
     * @param string $databaseName
     * @return \Ds\Illuminate\Database\MySqlSnapshot
     */
    public static function ensureLoaded(string $name, string $databaseName = null): MySqlSnapshot
    {
        return (new static(DB::connection(), $name, $databaseName))->ensureLoadedIntoDatabase();
    }

    /**
     * Get the snapshot name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the snapshot name.
     *
     * @param string $name
     * @return void
     */
    private function setName(string $name): void
    {
        $this->name = $name;
        $this->filename = base_path("database/snapshots/$name.sql");

        if (! file_exists($this->filename)) {
            throw new FileNotFoundException("Snapshot [$name] not found");
        }
    }

    /**
     * Load the snapshot into the database.
     *
     * @return \Ds\Illuminate\Database\MySqlSnapshot
     */
    public function loadIntoDatabase(): MySqlSnapshot
    {
        $db = $this->getMysqli();

        $db->query("DROP DATABASE IF EXISTS `{$this->databaseName}`");
        $db->query("CREATE DATABASE `{$this->databaseName}` COLLATE 'utf8mb4_unicode_ci'");

        new MySqlLoadFile($db, $this->filename, $this->databaseName);

        return $this;
    }

    /**
     * Ensure the snapshot is loaded into the database.
     *
     * @return \Ds\Illuminate\Database\MySqlSnapshot
     */
    public function ensureLoadedIntoDatabase(): MySqlSnapshot
    {
        if ($this->databaseExists()) {
            return $this;
        }

        return $this->loadIntoDatabase();
    }

    /**
     * Check if database exists.
     *
     * @return bool
     */
    private function databaseExists(): bool
    {
        $result = $this->getMysqli()->query(
            "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$this->databaseName}'"
        );

        return $result && $result->num_rows > 0;
    }

    /**
     * Get mysqli connection.
     *
     * @return \mysqli
     */
    private function getMysqli(): mysqli
    {
        if (empty($this->mysqli)) {
            $config = $this->connection->getConfig();

            $this->mysqli = new mysqli(
                $config['host'],
                $config['username'],
                $config['password'],
                '',
                $config['port']
            );

            $this->mysqli->set_charset($config['charset']);
        }

        return $this->mysqli;
    }
}
