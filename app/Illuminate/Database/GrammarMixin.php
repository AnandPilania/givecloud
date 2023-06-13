<?php

namespace Ds\Illuminate\Database;

use Illuminate\Support\Fluent;

/** @mixin \Illuminate\Database\Schema\Grammars\Grammar */
class GrammarMixin
{
    /**
     * Create the column definition for a custom type.
     */
    protected function typeCustom()
    {
        return function (Fluent $column) {
            if ($column->autoIncrement && ! in_array('custom', $this->serials)) {
                $this->serials[] = 'custom';
            }

            return $column->customName;
        };
    }

    /**
     * Create the column definition for an integer 11 type.
     */
    protected function typeInteger11()
    {
        return function () {
            if (! in_array('integer11', $this->serials, true)) {
                $this->serials[] = 'integer11';
            }

            return 'int(11)';
        };
    }
}
