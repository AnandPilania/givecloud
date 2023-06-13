<?php

namespace Ds\Http\Controllers\API\Imports;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Events\Imports\ImportUpdated;
use Ds\Http\Controllers\API\Controller;
use Ds\Models\Import;
use Ds\Models\Media;
use Ds\Services\Imports\ImportService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class FileController extends Controller
{
    public function __invoke(Import $import): JsonResponse
    {
        $file = request()->file('file');

        if (! in_array($file->guessClientExtension(), ['csv', 'xlsx', 'xls'])
            && ! in_array($file->getClientMimeType(), ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
        ) {
            return response()->json([
                'message' => 'The import file must be either a Microsoft Excel Open XML Format Spreadsheet (XLSX) or Comma-Separated Values (CSV) file.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $media = Media::storeUpload('file', [
                'collection_name' => 'imports',
                'visibility' => 'private',
            ]);

            if (! $media) {
                throw new MessageException('Failed to upload to Givecloud.');
            }

            $import->setImportFile($media);
            $import->resetSpreadSheetInfos(); // Refresh file_infos

            app(ImportService::class)->resetAnalysis($import);

            event(new ImportUpdated($import));

            return response()->json();
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'There was a problem with the file provided.' . $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
