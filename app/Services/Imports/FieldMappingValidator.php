<?php

namespace Ds\Services\Imports;

use Ds\Models\Import;
use Illuminate\Support\Facades\Validator;

class FieldMappingValidator
{
    public const ERROR_THRESHOLD = 0.1;

    public function validate(Import $import, string $field, string $mappedToColumn): array
    {
        $definition = collect($import->job->getColumnDefinitions())
            ->keyBy('id')
            ->get($field);

        if (! $definition) {
            return [
                'errors' => ['An error occurred, please try again'],
            ];
        }

        if (! $definition->validator) {
            return []; // No validation defined, accept everything.
        }

        $rowsInColumn = app(ImportService::class)->getRowsInColumn($import, $mappedToColumn);

        $errors = 0;

        $rows = $rowsInColumn
            ->mapWithKeys(function ($data, $line) use ($field, $definition, &$errors) {
                $validator = Validator::make([$field => $data], [$field => $definition->validator]);

                if ($validator->fails()) {
                    $errors++;
                }

                return [
                    $line => [
                        'data' => $data,
                        'hasErrors' => $validator->fails(),
                        'message' => $validator->errors()->first(),
                    ],
                ];
            });

        $nonEmptyRows = $rows->filter(fn ($r) => ! empty($r['data']));

        return [
            'nonEmptyRows' => $nonEmptyRows->count(),
            'rows' => $rows,
            'filtered' => $rows->filter(fn ($r) => ! empty($r['data']) || $r['hasErrors'])->values(),
            'hasErrors' => $errors > 0,
        ];
    }
}
