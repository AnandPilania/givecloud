<?php

namespace Ds\Http\Controllers\API\Imports;

use Ds\Events\Imports\ImportUpdated;
use Ds\Http\Controllers\API\Controller;
use Ds\Models\Import;
use Ds\Services\Imports\ImportService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MapFieldController extends Controller
{
    public const ERROR_THRESHOLD = 0.1;

    public function __invoke(Import $import): JsonResponse
    {
        $import->field_mapping = collect($import->field_mapping)->map(function ($row) {
            if ($row['id'] === request('fieldId')) {
                $row['column'] = request('mappedToColumn');
            }

            return $row;
        })->values();

        if (! $import->save()) {
            return response()->json(
                null,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        app(ImportService::class)->resetAnalysis($import);

        event(new ImportUpdated($import));

        return response()->json();
    }
}
