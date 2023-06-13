<?php

namespace Ds\Http\Controllers\API\Imports;

use Ds\Events\Imports\ImportUpdated;
use Ds\Http\Controllers\API\Controller;
use Ds\Models\Import;
use Ds\Services\Imports\ImportService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller
{
    public function get(Import $import): JsonResponse
    {
        return response()->json(app(ImportService::class)->toArray($import));
    }

    public function store(Import $import)
    {
        request()->whenFilled('file_has_headers', function () use ($import) {
            $import->file_has_headers = request('file_has_headers');
        });

        $saved = app(ImportService::class)->resetAnalysis($import);

        event(new ImportUpdated($import));

        return response()->json(
            null,
            $saved ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public function destroy(Import $import)
    {
        app(ImportService::class)->resetImport($import);

        event(new ImportUpdated($import));

        return response()->json();
    }

    public function start(Import $import)
    {
        $import->startImport();

        event(new ImportUpdated($import));

        return response()->json();
    }
}
