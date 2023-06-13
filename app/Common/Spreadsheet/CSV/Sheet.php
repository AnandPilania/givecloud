<?php

namespace Ds\Common\Spreadsheet\CSV;

use Box\Spout\Reader\CSV\Sheet as SpoutSheet;
use Ds\Common\Spreadsheet\SheetInterface;

class Sheet extends SpoutSheet implements SheetInterface
{
    /**
     * Count the rows without extracting any data.
     *
     * @return int
     */
    public function getTotalRows()
    {
        $count = 0;
        foreach ($this->getRowIterator() as $row) {
            $count++;
        }

        $this->getRowIterator()->rewind();

        return $count;
    }
}
