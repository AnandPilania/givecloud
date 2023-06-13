<?php

namespace Ds\Illuminate\Console;

/** @mixin \Illuminate\Console\Command */
class CommandMixin
{
    /**
     * Create a progress bar.
     */
    public function createProgressBar()
    {
        return function (int $max = 0, ?string $format = null) {
            return new ProgressBar($max, $format);
        };
    }
}
