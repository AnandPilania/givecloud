<?php

namespace Ds\Illuminate\Foundation\Bootstrap;

use ErrorException;
use Illuminate\Foundation\Bootstrap\HandleExceptions as Bootstrap;
use Illuminate\Support\Str;

class HandleExceptions extends Bootstrap
{
    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @param array $context
     * @return void
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        $warnings = [
            'Array to string conversion',
            'Attempt to read property',
            'Trying to access array offset on value of type',
            'Undefined array key',
            'Undefined variable',
        ];

        if (Str::startsWith($message, $warnings)) {
            notifyException(new ErrorException($message, 0, $level, $file, $line));

            return;
        }

        parent::handleError($level, $message, $file, $line, $context);
    }
}
