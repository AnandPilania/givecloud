<?php

namespace Ds\Common\DonorPerfect;

use Illuminate\Database\QueryException as IlluminateQueryException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Str;

class QueryException extends IlluminateQueryException
{
    /** @var \Illuminate\Http\Client\Response */
    public $response;

    /**
     * Create a new query exception instance.
     *
     * @param string $sql
     * @param array $bindings
     * @param \Exception $previous
     * @return void
     */
    public function __construct($sql, array $bindings, $previous)
    {
        parent::__construct($sql, $bindings, $previous);

        if ($previous instanceof RequestException) {
            $this->response = $previous->response;
        }
    }

    /**
     * Format the SQL error message.
     *
     * @param string $sql
     * @param array $bindings
     * @param \Exception $previous
     * @return string
     */
    protected function formatMessage($sql, $bindings, $previous)
    {
        $error = parent::formatMessage($sql, $bindings, $previous);

        // Strip out extraneous/inaccurate details from the error message
        return (string) Str::of($error)
            ->replace('[Microsoft][ODBC SQL Server Driver][SQL Server]', '')
            ->replaceMatches('/[\s]*Request failed\. Reason: (?:.*) \(SQL:/', ' (SQL:');
    }
}
