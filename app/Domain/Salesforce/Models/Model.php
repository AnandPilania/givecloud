<?php

namespace Ds\Domain\Salesforce\Models;

use Ds\Domain\Salesforce\Database\Builder as SalesforceBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Lester\EloquentSalesForce\Model as SalesforceModel;

abstract class Model extends SalesforceModel
{
    protected $externalKey = '';

    protected EloquentModel $model;

    abstract public function fields(): array;

    public function forModel(EloquentModel $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getCompoundKey()
    {
        return $this->model->getKey();
    }

    public function mapFields(bool $withAttributes = false): array
    {
        $fields = $this->fields();

        if (! $withAttributes) {
            return $fields;
        }

        return array_merge([
            'attributes' => [
                'type' => $this->getTable(),
                'referenceId' => $this->getCompoundKey(),
            ],
        ], $fields);
    }

    public function savesExternalReferenceLocally(): bool
    {
        return true;
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new SalesforceBuilder($query);
    }
}
