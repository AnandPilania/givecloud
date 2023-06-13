<?php

namespace Ds\Common\Spreadsheet;

use Box\Spout\Reader\SheetInterface as SpoutSheetInterface;

interface SheetInterface extends SpoutSheetInterface
{
    /**
     * Count the rows without extracting any data.
     *
     * @return int
     */
    public function getTotalRows();
}
