<?php

namespace Ds\Domain\Salesforce\Database;

use Lester\EloquentSalesForce\Database\SOQLBuilder;

class Builder extends SOQLBuilder
{
    public function upsertRecords(array $records): array
    {
        return app('forrest')->composite("sobjects/{$this->model->getTable()}/{$this->model->externalKey}", [
            'method' => 'PATCH',
            'body' => [
                'allOrNone' => false, // Should update fail entirely if one object fails to update
                'records' => $records,
            ],
        ]);
    }
}
