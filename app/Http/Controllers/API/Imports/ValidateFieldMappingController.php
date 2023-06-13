<?php

namespace Ds\Http\Controllers\API\Imports;

use Ds\Models\Import;
use Ds\Services\Imports\FieldMappingValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidateFieldMappingController
{
    public function __invoke(Import $import, Request $request): JsonResponse
    {
        $field = $request->get('fieldId');
        $mappedToColumn = $request->get('mappedTo');

        $result = app(FieldMappingValidator::class)->validate($import, $field, $mappedToColumn);

        return response()->json($result);
    }
}
