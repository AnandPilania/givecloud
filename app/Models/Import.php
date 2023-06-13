<?php

namespace Ds\Models;

use Carbon\Carbon;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Eloquent\Userstamps;
use Ds\Enums\Imports\Stage;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Jobs\Import\ContributionsFromFile;
use Ds\Jobs\Import\RecurringPaymentProfilesFromFile;
use Ds\Jobs\Import\SponsorshipsFromFile;
use Ds\Jobs\Import\Subscriber\Supporters;
use Ds\Jobs\Import\SupportersFromFile;
use Ds\Repositories\ImportRepository;
use Ds\Services\Imports\ChunkReadFilter;
use Ds\Services\Imports\ImportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Import extends Model
{
    use Userstamps;
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'imports';

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_complete' => false,
        'total_records' => 0,
        'current_record' => 1,
        'added_records' => 0,
        'updated_records' => 0,
        'skipped_records' => 0,
        'error_records' => 0,
        'import_messages' => '',
        'analysis_messages' => '',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'analysis_started_at',
        'analysis_ended_at',
        'import_started_at',
        'import_ended_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_complete' => 'boolean',
        'total_records' => 'integer',
        'current_record' => 'integer',
        'added_records' => 'integer',
        'updated_records' => 'integer',
        'skipped_records' => 'integer',
        'error_records' => 'integer',
        'field_mapping' => 'json',
        'file_infos' => 'json',
        'file_has_headers' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'file_path',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'log',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'progress',
        'estimated_minutes_remaining',
    ];

    /**
     * A list of possible import types.
     *
     * @var array
     */
    protected $importTypes = [
        'ContributionsFromFile' => ContributionsFromFile::class,
        'RecurringPaymentProfilesFromFile' => RecurringPaymentProfilesFromFile::class,
        'SupportersFromFile' => SupportersFromFile::class,
        'SponsorshipsFromFile' => SponsorshipsFromFile::class,
        'Supporters' => Supporters::class,
    ];

    public static array $subscriberFacingImportTypes = [
        'Supporters',
    ];

    /**
     * Relationship: File
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    /**
     * Set the import type.
     *
     * @param string $type
     */
    public function setImportTypeAttribute($type)
    {
        if (! array_key_exists($type, $this->importTypes)) {
            throw new InvalidArgumentException("Ds\Jobs\Import\\$type is not a recognized import type.");
        }

        $this->attributes['import_type'] = $type;

        if (empty($this->name)) {
            $this->name = $type;
        }
    }

    public function getIconAttribute(): string
    {
        $extension = pathinfo($this->file_name, PATHINFO_EXTENSION);

        if (in_array($extension, ['txt', 'csv'])) {
            return sprintf('/jpanel/assets/images/imports/%s.png', $extension);
        }

        if (in_array($extension, ['xls', 'xlsx'])) {
            return '/jpanel/assets/images/imports/spreadsheet.png';
        }

        return '/jpanel/assets/images/imports/csv.png';
    }

    public function getFriendlyNameAttribute(): ?string
    {
        if ($this->import_type === 'SupportersFromFile') {
            return 'Supporters from File';
        }

        return $this->import_type;
    }

    /**
     * Get the job class name
     */
    public function getJobClassNameAttribute()
    {
        return $this->importTypes[$this->import_type];
    }

    /**
     * Return the job
     *
     * @param \Ds\Jobs\Import\ImportJob $value
     */
    public function getJobAttribute($value)
    {
        if ($value) {
            return $value;
        }

        return app($this->job_class_name);
    }

    /**
     * Set the import file.
     *
     * @param \Ds\Models\Media $file
     */
    public function setImportFile(Media $file)
    {
        $this->media_id = $file->id;
        $this->file_name = $file->filename;
        $this->file_path = $file->filename;
    }

    public function getCurrentStageAttribute(): string
    {
        if (in_array($this->stage, Stage::cases())) {
            return $this->stage;
        }

        if ($this->stage === '') {
            return Stage::DRAFT;
        }
    }

    /**
     * Attribute Mask: progress
     *
     * @return float|int
     */
    public function getProgressAttribute()
    {
        if ($this->is_complete) {
            return 100;
        }

        if ($this->total_records) {
            return ($this->current_record / $this->total_records) * 100;
        }

        // use baseline total for progress if the total number
        // of records could not be calculated for what ever reason
        $baselineTotal = 20000;

        // once the import has gone past 98% of the baseline total
        // the process indicator should stay at 98% until completed
        if ($this->current_record > $baselineTotal * 0.98) {
            return 98;
        }

        return ($this->current_record / $baselineTotal) * 100;
    }

    /**
     * Attribute Mask: estimated_minutes_remaining
     *
     * @return float|null
     */
    public function getEstimatedMinutesRemainingAttribute()
    {
        if ($this->is_complete || ! $this->started_at) {
            return null;
        }

        if ($this->stage === 'import_queue') {
            return null;
        }

        if ($this->stage === 'analyzing' && $this->analysis_started_at) {
            $percent = $this->progress;
            $elapsed = $this->analysis_started_at->diffInSeconds() / 60;
        } elseif ($this->import_started_at) {
            $percent = $this->progress;
            $elapsed = $this->import_started_at->diffInSeconds() / 60;
        } else {
            return null;
        }

        // only display an estimate after sufficient
        // time has past to provide a relatively accurate estimate
        if (! $percent || $elapsed < 0.5) {
            return null;
        }

        return round((100 - $percent) * ($elapsed / $percent), 1);
    }

    /**
     * Retrieve list of column headers for import.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getColumnHeadersAttribute()
    {
        return app(ImportRepository::class)->getHeaders($this->import_type);
    }

    public function scopeSubscriberFacing(Builder $query): Builder
    {
        return $query->whereIn('name', self::$subscriberFacingImportTypes);
    }

    public function scopeSupportFacing(Builder $query): Builder
    {
        return $query->whereNotIn('name', self::$subscriberFacingImportTypes);
    }

    /**
     * Analyze the import
     */
    public function analyze()
    {
        if ($this->analysis_started_at) {
            throw new MessageException('Analysis can not be started again.');
        }

        $this->analysisMessage('Analysis initiated');
        $this->stage = 'analysis_queue';
        $this->analysis_started_at = now();
        $this->save();

        $this->dispatchAnalysisJob();
    }

    public function asSpreadsheet(): Worksheet
    {
        if (empty($this->file)) {
            throw new MessageException('Import file not found');
        }

        $file = $this->file->getCachedTemporaryFile();

        $spreadsheet = IOFactory::load($file);
        $spreadsheet->getWorksheetIterator()->rewind();

        return $spreadsheet->getActiveSheet();
    }

    public function asSpreadsheetSample(): ?Worksheet
    {
        if (empty($this->file)) {
            return null;
        }

        return Cache::tags(['imports'])->remember('imports:sample:' . $this->file->id, now()->addDay(), function () {
            $file = $this->file->getCachedTemporaryFile();

            $reader = IOFactory::createReaderForFile($file);

            $reader->setReadDataOnly(true);
            $reader->setReadFilter((new ChunkReadFilter)->maxRows(1000));

            $spreadsheet = $reader->load($file);
            $spreadsheet->getWorksheetIterator()->rewind();

            return  $spreadsheet->getActiveSheet();
        });
    }

    public function resetSpreadSheetInfos(): array
    {
        $this->file_infos = null;

        $this->field_mapping = app(ImportService::class)->dataSnapshot($this)->map(function ($field) {
            return [
                'id' => $field->id,
                'column' => $field->mappedTo,
                'guessed' => $field->guessed,
            ];
        });

        return $this->spreadSheetInfos();
    }

    public function spreadSheetInfos(): array
    {
        if ($this->file_infos) {
            return $this->file_infos;
        }

        $file = $this->file->getCachedTemporaryFile();
        $this->file_infos = IOFactory::createReaderForFile($file)->listWorksheetInfo($file);
        $this->total_records = $this->file_infos[0]['totalRows'] - (int) $this->file_has_headers;
        $this->save();

        return $this->file_infos;
    }

    /**
     * Start the import
     */
    public function startImport()
    {
        if ($this->started_at) {
            throw new MessageException('Analysis can not be started again.');
        }

        $this->importMessage('Import initiated');
        $this->stage = 'import_queue';
        $this->started_at = now();
        $this->save();

        $this->dispatchImportJob();
    }

    /**
     * Dispatches the import job
     */
    protected function dispatchAnalysisJob()
    {
        if (dispatch(new $this->job_class_name($this))) {
            $this->stage = 'analysis_queue';
            $this->analysisMessage('Analysis has been queued for processing.');
            $this->save();
        }
    }

    /**
     * Dispatches the import job
     */
    protected function dispatchImportJob()
    {
        if (dispatch(new $this->job_class_name($this))) {
            $this->stage = 'import_queue';
            $this->importMessage('Import has been queued for processing.');
            $this->save();
        }
    }

    /**
     * Add to the import messages
     *
     * @param string $comment
     * @param bool $save
     * @return bool|null
     */
    public function importMessage($comment, $save = false)
    {
        $comment = trim($comment);

        if (empty($comment)) {
            return true;
        }

        $this->import_messages .= fromLocal('now')->toTimeString() . " - {$comment}\n";

        if ($save) {
            return $this->save();
        }
    }

    /**
     * Add to the analaysis messages
     *
     * @param string $comment
     * @param bool $save
     * @return bool|null
     */
    public function analysisMessage($comment, $save = false)
    {
        $comment = trim($comment);

        if (empty($comment)) {
            return true;
        }

        $this->analysis_messages .= fromLocal('now')->toTimeString() . " - {$comment}\n";

        if ($save) {
            return $this->save();
        }
    }

    /**
     * Abort an import during any stage of the import process.
     */
    public function abort()
    {
        // abort during analysis
        if (in_array($this->stage, ['analyzing', 'analysis_queue'])) {
            $this->analysisMessage('Aborted by ' . user()->full_name . '.');
            $this->analysis_ended_at = ($this->analysis_ended_at ?? Carbon::now());

        // abort during import
        } else {
            $this->importMessage('Aborted by ' . user()->full_name . '.');
            $this->ended_at = ($this->ended_at ?? Carbon::now());
        }

        $this->stage = 'aborted';
        $this->save();
    }

    /**
     * Add an error to the log
     *
     * @param \Exception|string $error
     * @param bool $save
     * @return bool
     */
    public function error($error, $save = true)
    {
        if (is_a($error, 'Exception')) {
            $error = $error->getMessage() . ' (' . $error->getLine() . ')';
        }

        $this->status_message = trim($error);

        return $this->importMessage("ROW: {$this->current_record}  ERROR: {$this->error_message}\n", $save);
    }

    /**
     * Incrememnt our progress
     *
     * @return bool
     */
    public function nextRecord($column = 'added_records')
    {
        $this->current_record++;
        $this->{$column}++;

        return $this->save();
    }

    public function startAnalysis(): self
    {
        if (empty($this->file)) {
            throw new MessageException('Import file not found');
        }

        if (empty($this->field_mapping)) {
            throw new MessageException('No fields have been mapped');
        }

        $this->stage = 'analyzing';
        $this->current_record = 0;
        $this->analysisMessage("Starting to read file {$this->file_path}", true);

        $this->save();

        return $this;
    }

    /**
     * Finish an import
     *
     * @param string $message
     * @return bool
     */
    public function finishAnalysis($message = 'Finished.')
    {
        if ($this->stage === 'import_ready') {
            return true;
        }

        $this->is_complete = false;
        $this->stage = 'import_ready';
        $this->analysis_ended_at = now();
        $this->analysisMessage($message);

        return $this->save();
    }

    /**
     * Finish an import
     *
     * @param string $message
     * @return bool
     */
    public function finishImport($message = 'Finished.')
    {
        if ($this->stage === 'done') {
            return true;
        }

        $this->is_complete = true;
        $this->stage = 'done';
        $this->ended_at = now();
        $this->importMessage($message);

        return $this->save();
    }

    /**
     * Finish an import w/ an error
     *
     * @param string $message
     */
    public function finishWithError($message)
    {
        $this->error($message);
        $this->finish('Finished with errors.');
    }
}
