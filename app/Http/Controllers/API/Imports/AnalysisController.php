<?php

namespace Ds\Http\Controllers\API\Imports;

use Ds\Events\Imports\ImportUpdated;
use Ds\Models\Import;
use Ds\Services\Imports\ImportService;

class AnalysisController
{
    public function destroy(Import $import)
    {
        app(ImportService::class)->resetAnalysis($import);

        event(new ImportUpdated($import));

        return response()->json();
    }

    public function store(Import $import)
    {
        rescue(fn () => $import->analyze());

        event(new ImportUpdated($import));

        return response()->json();
    }
}
