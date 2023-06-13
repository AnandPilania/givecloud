<?php

namespace Ds\Common\DonorPerfect;

use Ds\Domain\Shared\Exceptions\MessageException;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\SqlServerProcessor;

class Processor extends SqlServerProcessor
{
    /**
     * Process an "insert get ID" query.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $sql
     * @param array $values
     * @param string $sequence
     * @return int
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        throw new MessageException(
            'Inserts for DPO entities MUST be made using their associated procedure.'
        );
    }
}
