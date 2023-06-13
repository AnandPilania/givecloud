<?php

namespace Ds\Domain\Shared;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use InvalidArgumentException;
use LiveControl\EloquentDataTable\DataTable as EloquentDataTable;

class DataTable extends EloquentDataTable
{
    /** @var int */
    private $cachedCount = null;

    private $withManualCount = false;

    public function withManualCount()
    {
        $this->withManualCount = true;

        return $this;
    }

    /**
     * @return array
     */
    public function make()
    {
        $results = parent::make();

        // clear cache so that subsequent make calls get a fresh
        // count, event though make should only ever be called once
        $this->cachedCount = null;

        if (! $this->withManualCount) {
            return $results;
        }

        $offset = (int) request('start', 0);
        $pageSize = (int) request('length', 50);
        $total = count($results['data']);

        if ($total === $pageSize) {
            $total++;
        }

        return array_merge($results, [
            'recordsFiltered' => $offset + $total,
            'recordsTotal' => $offset + $total,
        ]);
    }

    /**
     * The LiveControl library performs always performs a second count
     * to try get the count after applying filters. Even when there's no
     * filters applied. This results in a pointless potentially expensive
     * database query. Especially so for us since we manually apply all
     * our own filters.
     *
     * Therefore we're caching the result the count method.
     *
     * @return int
     */
    protected function count()
    {
        if ($this->withManualCount) {
            return 0;
        }

        if ($this->cachedCount) {
            return $this->cachedCount;
        }

        return $this->cachedCount = parent::count();
    }

    /**
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Model $builder
     * @return $this
     *
     * @throws \Exception
     */
    public function setBuilder($builder)
    {
        if (! is_instanceof($builder, [Builder::class, EloquentBuilder::class, Model::class])) {
            throw new InvalidArgumentException('$builder variable is not an instance of Builder or Model.');
        }

        $this->builder = $builder;

        return $this;
    }
}
