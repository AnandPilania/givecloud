<?php

namespace Ds\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

abstract class Repository
{
    /** @var \Ds\Illuminate\Database\Eloquent\Model */
    protected $model;

    public function get(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }
}
