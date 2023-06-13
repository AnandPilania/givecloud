<?php

namespace Ds\Jobs\Import;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Jobs\Job;
use Ds\Models\Import;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Throwable;

abstract class ImportJob extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * Import model
     *
     * @var \Ds\Models\Import
     */
    protected $import;

    /**
     * Import model
     *
     * @var \Ds\Common\Spreadsheet\SheetInterface
     */
    protected $reader;

    /**
     * Create a new job instance.
     *
     * @param \Ds\Models\Import $import
     * @return void
     */
    public function __construct(Import $import)
    {
        $this->import = $import;
    }

    /**
     * Column definition.
     */
    abstract public function getColumnDefinitions(): Collection;

    /**
     * Analyze a row.
     *
     * @param array $row
     */
    abstract public function analyzeRow(array $row);

    /**
     * Import a row.
     *
     * @param array $row
     */
    abstract public function importRow(array $row);

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (in_array($this->import->stage, ['analysis_queue', 'draft'])) {
            $this->handleAnalysis();
        } elseif ($this->import->stage == 'import_queue') {
            $this->handleImport();
        }
    }

    /**
     * Execute the analysis.
     *
     * @return void
     */
    public function handleAnalysis()
    {
        $this->import->stage = 'analyzing';
        $this->import->current_record = 0;
        $this->import->save();

        try {
            $reader = $this->getReader();

            if (empty($this->import->file)) {
                throw new MessageException('Import file not found');
            }

            $this->import->analysisMessage("Starting to read file {$this->import->file_path}", true);

            $file = $this->import->file->getAsTemporaryFile();
            $reader->open($file->getFilename());

            $reader->getSheetIterator()->rewind();
            $sheet = $reader->getSheetIterator()->current();

            $this->import->analysisMessage('Estimating approximate number of rows.', true);
            $this->import->total_records = $sheet->getTotalRows();
            $this->import->save();

            foreach ($sheet->getRowIterator() as $index => $row) {
                // skip first row
                if ($this->shouldSkipFirstRow($index)) {
                    continue;
                }

                try {
                    $message = $this->analyzeRow($this->validateRow($row));
                    if ($message) {
                        $this->import->analysisMessage('Row ' . ($index) . ': ' . $message);
                    }
                    $this->import->nextRecord('analyzed_ok_records');
                } catch (Throwable $e) {
                    $this->import->analysisMessage('Row ' . ($index) . ' ERROR: ' . $e->getMessage() . ' (' . $e->getLine() . ')');
                    $this->import->nextRecord('analyzed_warning_records');
                }

                // if we're getting 45% errors after the first 20 records, bail
                if ($index > 20 && ($this->import->analyzed_warning_records / $index) >= 0.45) {
                    throw new MessageException('Error rate too high. Try making corrections to the file based on the feedback so far and re-upload the file.');
                }
            }

            $reader->close();
            $this->import->finishAnalysis();
        } catch (Throwable $e) {
            if (isset($reader)) {
                $reader->close();
            }

            $this->import->analysisMessage($e->getMessage());
            $this->import->stage = 'aborted';
            $this->import->status_message = $e->getMessage();
            $this->import->save();
        }
    }

    /**
     * Execute the import.
     *
     * @return void
     */
    public function handleImport()
    {
        $this->import->stage = 'importing';
        $this->import->current_record = 0;
        $this->import->status_message = null;
        $this->import->save();

        try {
            $reader = $this->getReader();

            if (empty($this->import->file)) {
                throw new MessageException('Import file not found');
            }

            $this->import->importMessage("Starting to read file {$this->import->file_path}", true);

            $file = $this->import->file->getAsTemporaryFile();
            $reader->open($file->getFilename());

            $reader->getSheetIterator()->rewind();
            $sheet = $reader->getSheetIterator()->current();

            if (! $this->import->total_records) {
                $this->import->importMessage('Estimating approximate number of rows.', true);
                $this->import->total_records = $sheet->getTotalRows();
                $this->import->save();
            }

            foreach ($sheet->getRowIterator() as $index => $row) {
                // skip first row
                if ($this->shouldSkipFirstRow($index)) {
                    continue;
                }

                try {
                    $this->import->nextRecord($this->importRow($this->validateRow($row)));
                } catch (Throwable $e) {
                    $this->import->importMessage('Row ' . ($index) . ' ERROR: ' . $e->getMessage() . ' (' . $e->getLine() . ')');
                    $this->import->nextRecord('error_records');
                }
            }

            $reader->close();
            $this->import->finishImport();
        } catch (Throwable $e) {
            if (isset($reader)) {
                $reader->close();
            }

            $this->import->importMessage($e->getMessage());
            $this->import->stage = 'aborted';
            $this->import->status_message = $e->getMessage();
            $this->import->save();
        }
    }

    /**
     * Get an instance of the appropriate reader, given the type of the file to be read
     *
     * @return \Box\Spout\Reader\ReaderInterface
     */
    public function getReader()
    {
        if (! $this->reader) {
            $extension = strtolower(pathinfo($this->import->file_path, PATHINFO_EXTENSION));
            $this->reader = app('spreadsheet.reader')->create($extension);
        }

        return $this->reader;
    }

    /**
     * Perform basic validations and import the file.
     *
     * @param array $row
     * @return array
     */
    public function validateRow(array &$row)
    {
        $expected_columns = $this->import->job->getColumnDefinitions();
        $data = [];
        $validators = [];
        $messages = [];
        $pretty_attributes = [];

        foreach ($row as $ix => $column) {
            if (! isset($expected_columns[$ix])) {
                $messages[] = 'Ignoring data in column ' . ($ix + 1);

                continue;
            }

            $col_def = $expected_columns[$ix];

            if ($col_def->sanitize ?? false) {
                $data[$col_def->id] = $this->sanitizeValue($column, $col_def->sanitize);
            } else {
                $data[$col_def->id] = $this->sanitizeValue($column);
            }

            if ($col_def->validator ?? false) {
                $validators[$col_def->id] = $col_def->validator;
                $pretty_attributes[$col_def->id] = $col_def->name;
            }

            // couldn't get this working
            // if ($col_def->messages ?? false) {
            //  $messages = array_merge($messages, [
            //      $col_def->id => $col_def->messages
            //  ]);
            // }

            // set default value (if provided)
            if ($data[$col_def->id] === null && isset($col_def->default)) {
                if (is_callable($col_def->default)) {
                    $data[$col_def->id] = $col_def->default->call($this, $data);
                } else {
                    $data[$col_def->id] = $col_def->default;
                }
            }
        }

        $validator = Validator::make($data, $validators, $messages);
        $validator->setAttributeNames($pretty_attributes);
        $all_validation_errors = $validator->errors()->all();

        // if there are validation errors
        if (count($all_validation_errors) > 0) {
            throw new MessageException(implode(' ', $all_validation_errors));
        }

        $row = $data;

        return $row;
    }

    /**
     * Clean up a cell value.
     *
     * @return string|null
     */
    protected function sanitizeValue($value, $filter = FILTER_DEFAULT)
    {
        if (is_string($value)) {
            $value = trim($value);

            return ($value === '') ? null : filter_var($value, $filter);
        }

        if (is_numeric($value)) {
            return filter_var($value, $filter);
        }
    }

    protected function shouldSkipFirstRow($index): bool
    {
        if (! in_array($this->import->name, Import::$subscriberFacingImportTypes, true)) {
            return $index === 1;
        }

        if ($this->import->file_has_headers) {
            return $index === 1;
        }

        return false;
    }
}
