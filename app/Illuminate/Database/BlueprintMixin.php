<?php

namespace Ds\Illuminate\Database;

/** @mixin \Illuminate\Database\Schema\Blueprint */
class BlueprintMixin
{
    /**
     * Create a new custom column on the table.
     */
    public function custom()
    {
        return function ($column, $customName, $autoIncrement = false) {
            return $this->addColumn('custom', $column, compact('customName', 'autoIncrement'));
        };
    }

    /**
     * Create a new unsigned integer 11 column on the table.
     */
    public function unsignedInteger11()
    {
        return function ($column, $autoIncrement = false) {
            $unsigned = true;

            return $this->addColumn('integer11', $column, compact('autoIncrement', 'unsigned'));
        };
    }
}
