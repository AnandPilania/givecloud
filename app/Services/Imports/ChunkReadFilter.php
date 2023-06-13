<?php

namespace Ds\Services\Imports;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ChunkReadFilter implements IReadFilter
{
    protected int $maxRows = 200;

    public function maxRows(int $maxRows): self
    {
        $this->maxRows = $maxRows;

        return $this;
    }

    public function readCell($column, $row, $worksheetName = '')
    {
        return $row < $this->maxRows;
    }
}
