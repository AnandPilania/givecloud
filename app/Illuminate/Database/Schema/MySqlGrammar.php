<?php

namespace Ds\Illuminate\Database\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\MySqlGrammar as Grammar;
use Illuminate\Support\Fluent;

class MySqlGrammar extends Grammar
{
    /** @var bool */
    public static $inplaceLockNone = true;

    /**
     * Compile an add column command.
     *
     * @param \Illuminate\Database\Schema\Blueprint $blueprint
     * @param \Illuminate\Support\Fluent $command
     * @return array
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        $commands = parent::compileAdd($blueprint, $command);

        if (static::$inplaceLockNone) {
            $commands[0] .= ', algorithm=inplace, lock=none';
        }

        return $commands;
    }
}
