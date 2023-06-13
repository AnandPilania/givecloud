<?php

namespace Ds\Enums\Imports;

class Stage
{
    public const ABORTED = 'aborted';
    public const DONE = 'done';
    public const DRAFT = 'draft';

    public const ANALYSIS_QUEUE = 'analysis_queue';
    public const ANALYZING = 'analyzing';
    public const ANALYSIS_WARNING = 'analysis_warning';
    public const ANALYSIS_ERROR = 'analysis_error';

    public const IMPORT_QUEUE = 'import_queue';
    public const IMPORT_READY = 'import_ready';
    public const IMPORTING = 'importing';

    public static function cases(): array
    {
        return [
            self::ABORTED,
            self::DONE,
            self::DRAFT,
            self::ANALYSIS_QUEUE,
            self::ANALYZING,
            self::ANALYSIS_WARNING,
            self::ANALYSIS_ERROR,
            self::IMPORT_QUEUE,
            self::IMPORT_READY,
            self::IMPORTING,
        ];
    }
}
