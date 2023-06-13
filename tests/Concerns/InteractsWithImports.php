<?php

namespace Tests\Concerns;

use DomainException;
use Ds\Jobs\Import\ImportJob;
use Illuminate\Support\Facades\Validator;

trait InteractsWithImports
{
    private function assertImportJobColumnDefinitions(ImportJob $importJob): void
    {
        $importJob->getColumnDefinitions()->each(function ($column) {
            $this->assertInstanceOf(\stdClass::class, $column);

            $validator = Validator::make((array) $column, [
                'id' => 'required|regex:/^([a-z][0-9]?)+(?:_[a-z0-9]+)*$/',
                'name' => 'required|string',
                'validator' => 'nullable',
                'default' => 'nullable',
                'hint' => 'nullable|string',
                'sanitize' => 'nullable|int',
            ]);

            if ($validator->fails()) {
                throw new DomainException($validator->errors()->first());
            }
        });
    }
}
