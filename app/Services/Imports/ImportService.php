<?php

namespace Ds\Services\Imports;

use Ds\Models\Import;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportService
{
    public const ROWS_TO_SCAN = 100;

    public function firstRowOfData(Import $import): int
    {
        return $import->file_has_headers ? 2 : 1;
    }

    public function getRowsInColumn(Import $import, $columnIndex): Collection
    {
        $sheet = $import->asSpreadsheetSample();

        $range = $sheet->shrinkRangeToFit(sprintf(
            '%s%d:%s%d',
            $columnIndex,
            $this->firstRowOfData($import),
            $columnIndex,
            min($sheet->getHighestRow(), self::ROWS_TO_SCAN)
        ));

        return collect($import->asSpreadsheet()->rangeToArray($range))->flatten();
    }

    public function dataSnapshot(Import $import): Collection
    {
        $mapping = $this->mapColumnDefintions($import);

        $snapshot = $this->sheetSnapshot($import);

        $headers = data_get($snapshot, 'headers.*.header', []);

        return collect($mapping)->map(function ($column) use ($headers) {
            if (optional($column)->guessed) {
                return $column;
            }

            $column->guessed = in_array($column->id, $headers, true) ? $column->id : '';

            return $column;
        });
    }

    public function sheetSnapshot(Import $import): Collection
    {
        $sampleSheet = $import->asSpreadsheetSample();

        if (! $sampleSheet) {
            return collect();
        }

        $info = $import->spreadSheetInfos();

        $rows = collect();

        foreach ($sampleSheet->getColumnIterator() as $column) {
            $rows->push([
                'column' => $column->getColumnIndex(),
                'header' => $sampleSheet->getCell($column->getColumnIndex() . '1')->getCalculatedValue(),
                'rows' => $sampleSheet->rangeToArray($column->getColumnIndex() . '2:' . $column->getColumnIndex() . '20'),
            ]);
        }

        $rows = $rows->filter(function ($header) {
            return $header['header'] !== null;
        });

        return collect([
            'name' => $import->file_name,
            'title' => $sampleSheet->getTitle(),
            'rows' => $info[0]['totalRows'],
            'columns' => $info[0]['totalColumns'],
            'headers' => $rows->all(),
        ]);
    }

    public function mapColumnDefintions(Import $import): Collection
    {
        $mapping = collect($import->field_mapping ?? [])->keyBy('id');

        return $import->job->getColumnDefinitions()->map(function ($column) use ($mapping) {
            $column->mappedTo = data_get($mapping, $column->id . '.column');

            return $column;
        });
    }

    public function resetAnalysis(Import $import): bool
    {
        $import->analysis_started_at = null;
        $import->analysis_ended_at = null;
        $import->analyzed_ok_records = 0;
        $import->analyzed_warning_records = 0;
        $import->analysis_messages = '';
        $import->current_record = 0;
        $import->stage = 'draft';

        return $import->save();
    }

    public function resetImport(Import $import): bool
    {
        $import->current_record = 0;
        $import->error_records = 0;
        $import->added_records = 0;
        $import->updated_records = 0;
        $import->skipped_records = 0;
        $import->stage = 'import_ready';
        $import->started_at = null;
        $import->ended_at = null;
        $import->import_messages = null;
        $import->import_messages_count = 0;

        return $import->save();
    }

    public function getStepForStage(Import $import): int
    {
        if ($import->stage === 'done') {
            return 4;
        }

        if (in_array($import->stage, ['analysis_queue', 'aborted'])) {
            return 3;
        }

        if (! $import->file) {
            return 1;
        }

        return 2;
    }

    public function getInfosFromFile($file): array
    {
        return IOFactory::createReaderForFile($file)->listWorksheetInfo($file);
    }

    public function toArray(Import $import): array
    {
        return [
            'import' => $import->toArray(),
            'sheet' => app(ImportService::class)->sheetSnapshot($import),
            'data' => app(ImportService::class)->dataSnapshot($import),
        ];
    }
}
