<?php

namespace Ds\Jobs\Import\Subscriber;

use Ds\Events\Imports\RowWasUpdated;
use Ds\Jobs\Import\SupportersFromFile;
use Ds\Services\Imports\ImportService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;

class Supporters extends SupportersFromFile
{
    private $mappedColumns;

    public function handleAnalysis()
    {
        $this->analyzedRows = [];
        $this->import->startAnalysis();

        try {
            $sheet = $this->import->asSpreadsheet();

            foreach ($sheet->getRowIterator() as $row) {
                if ($this->import->file_has_headers && $row->getRowIndex() === 1) {
                    continue;
                }

                try {
                    if ($message = $this->analyzeRow($this->validateWorksheetRow($row))) {
                        $this->import->analysisMessage('Row ' . ($row->getRowIndex()) . ': ' . $message);
                    }

                    $this->import->nextRecord('analyzed_ok_records');
                } catch (Exception $e) {
                    $this->import->analysisMessage(sprintf(
                        'Row %s ERROR: %s (%s[%s])',
                        $row->getRowIndex(),
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    ));
                    $this->import->nextRecord('analyzed_warning_records');
                }

                event(new RowWasUpdated($this->import));

                // if we're getting 45% errors after the first 20 records, bail
                if ($row->getRowIndex() > 20 && ($this->import->analyzed_warning_records / $row->getRowIndex()) >= 0.45) {
                    throw new Exception('Error rate too high. Try making corrections to the file based on the feedback so far and re-upload the file.');
                }
            }

            $this->import->finishAnalysis();
        } catch (Exception $e) {
            $this->import->analysisMessage($e->getMessage());
            $this->import->stage = 'aborted';
            $this->import->status_message = $e->getMessage();
            $this->import->save();
        }
    }

    public function handleImport()
    {
        $this->import->stage = 'importing';
        $this->import->current_record = 0;
        $this->import->status_message = null;
        $this->import->save();

        try {
            $sheet = $this->import->asSpreadsheet();

            $this->import->importMessage("Starting to read file {$this->import->file_path}", true);

            foreach ($sheet->getRowIterator() as $row) {
                if ($this->import->file_has_headers && $row->getRowIndex() === 1) {
                    continue;
                }

                try {
                    $this->import->nextRecord($this->importRow($this->validateWorksheetRow($row)));
                } catch (Exception $e) {
                    $this->import->importMessage('Row ' . ($row->getRowIndex()) . ' ERROR: ' . $e->getMessage() . ' (' . $e->getLine() . ')');
                    $this->import->nextRecord('error_records');
                }

                event(new RowWasUpdated($this->import));
            }

            $this->import->finishImport();
        } catch (Exception $e) {
            $this->import->importMessage($e->getMessage());
            $this->import->stage = 'aborted';
            $this->import->status_message = $e->getMessage();
            $this->import->save();
        }
    }

    public function validateWorksheetRow(Row $row): array
    {
        $mappedColumns = $this->mappedColumns();

        $data = $messages = $prettyAttributes = [];

        foreach ($row->getCellIterator() as $cell) {
            // Skip columns that are not mapped
            if (! $columnDefinition = $mappedColumns->get($cell->getColumn())) {
                continue;
            }

            $data[$columnDefinition->id] = $this->sanitizeValue($cell->getValue(), $columnDefinition->sanitize ?? FILTER_DEFAULT);

            if ($columnDefinition->validator ?? false) {
                $validators[$columnDefinition->id] = $columnDefinition->validator;
                $prettyAttributes[$columnDefinition->id] = $columnDefinition->name;
            }
        }

        $validator = Validator::make($data, $validators, $messages);
        $validator->setAttributeNames($prettyAttributes);

        if ($validator->fails()) {
            throw new Exception(implode(' ', $validator->errors()->all()));
        }

        return $data;
    }

    protected function mappedColumns(): Collection
    {
        if ($this->mappedColumns) {
            return $this->mappedColumns;
        }

        return $this->mappedColumns = app(ImportService::class)
            ->mapColumnDefintions($this->import)
            ->filter(function ($columns) {
                return $columns->mappedTo;
            })->keyBy('mappedTo');
    }
}
