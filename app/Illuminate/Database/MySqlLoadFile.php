<?php

namespace Ds\Illuminate\Database;

use Ds\Domain\Shared\Exceptions\MessageException;
use Illuminate\Database\MySqlConnection;
use mysqli;

class MySqlLoadFile
{
    /** @var \mysqli */
    private $mysqli;

    /** @var resource */
    private $file;

    /** @var string */
    private $query = '';

    /** @var int */
    private $offset = 0;

    /** @var string */
    private $delimiter = ';';

    /** @var string */
    private $space = '(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)';

    /** @var string */
    private $parse = '[\'"`#]|/\*|-- |$';

    /** @var int */
    private $pos;

    /**
     * @param \mysqli $mysqli
     * @param string $filename
     * @param string|null $databaseName
     */
    public function __construct(mysqli $mysqli, string $filename, ?string $databaseName = null)
    {
        $this->mysqli = $mysqli;

        if ($databaseName) {
            $this->mysqli->select_db($databaseName);
        }

        $this->loadFile($filename);
    }

    /**
     * Imports an SQL file into a site's database.
     *
     * @param \Illuminate\Database\MySqlConnection $connection
     * @param string $filename
     * @param string|null $databaseName
     * @return \Ds\Illuminate\Database\MySqlLoadFile
     */
    public static function load(MySqlConnection $connection, string $filename, ?string $databaseName = null): MySqlLoadFile
    {
        $config = $connection->getConfig();

        $mysqli = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $config['port']
        );

        $mysqli->set_charset($config['charset']);

        $instance = new static($mysqli, $filename, $databaseName);

        $mysqli->close();

        return $instance;
    }

    /**
     * @param string $filename
     * @return void
     */
    private function loadFile(string $filename): void
    {
        if (is_file($filename) === false || ($this->file = fopen($filename, 'rb')) === false) {
            throw new MessageException('Unable to open the sql file.');
        }

        if (! $this->file || ($this->query = fread($this->file, 1e6)) === false) {
            throw new MessageException('Unable to read the sql file.');
        }

        while ($this->query != '') {
            if ($this->delimiterStatementFound()) {
                continue;
            }

            $found = $this->checkForDelimiterCommentOrBlankLine();

            if (! $found && $this->file && ! feof($this->file)) {
                $this->query .= fread($this->file, 1e5);

                continue;
            }

            if (! $found && rtrim($this->query) === '') {
                break;
            }

            $this->offset = $this->pos + strlen($found);

            if ($found && rtrim($found) !== $this->delimiter) {
                $this->matchQuoteOrCommentEnd($found);

                continue;
            }

            $this->executeQuery();
        }

        if (is_resource($this->file)) {
            fclose($this->file);
        }
    }

    /**
     * @return bool
     */
    private function delimiterStatementFound(): bool
    {
        if (! $this->offset && preg_match("~^{$this->space}*+DELIMITER\\s+(\\S+)~i", $this->query, $match)) {
            $this->delimiter = $match[1];
            $this->query = substr($this->query, strlen($match[0]));

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    private function checkForDelimiterCommentOrBlankLine(): string
    {
        // should always match
        preg_match('(' . preg_quote($this->delimiter) . "\\s*|{$this->parse})", $this->query, $match, PREG_OFFSET_CAPTURE, $this->offset);

        [$found, $this->pos] = $match[0];

        return $found;
    }

    /**
     * @param string $openingDelimiter
     * @return void
     */
    private function matchQuoteOrCommentEnd(string $openingDelimiter): void
    {
        if ($openingDelimiter === '/*') {
            $closingDelimiter = '\*/';
        } elseif ($openingDelimiter === '[') {
            $closingDelimiter = ']';
        } elseif (preg_match('~^-- |^#~', $openingDelimiter)) {
            $closingDelimiter = "\n";
        } else {
            $closingDelimiter = preg_quote($openingDelimiter) . '|\\\\.';
        }

        while (preg_match("($closingDelimiter|$)s", $this->query, $match, PREG_OFFSET_CAPTURE, $this->offset)) {
            $found = $match[0][0];
            if (! $found && $this->file && ! feof($this->file)) {
                $this->query .= fread($this->file, 1e5);
            } else {
                $this->offset = $match[0][1] + strlen($found);
                if ($found[0] !== '\\') {
                    break;
                }
            }
        }
    }

    /**
     * @return void
     */
    private function executeQuery(): void
    {
        $query = substr($this->query, 0, $this->pos);

        $this->mysqli->multi_query($query);

        do {
            $this->mysqli->store_result(); // do nothing with result

            if ($this->mysqli->error) {
                throw new MessageException(sprintf(
                    'Error in query (%s): %s',
                    $this->mysqli->errno,
                    $this->mysqli->error
                ));
            }
        } while ($this->mysqli->more_results() && $this->mysqli->next_result());

        $this->query = substr($this->query, $this->offset);
        $this->offset = 0;
    }
}
