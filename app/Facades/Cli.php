<?php

namespace Ds\Facades;

use Ds\Common\CommandLine;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string run($command)
 * @method static string runQuietly($command)
 *
 * @see \Ds\Common\CommandLine
 */
class Cli extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return CommandLine::class;
    }
}
