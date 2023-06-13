<?php

namespace Ds\Common\Spreadsheet\CSV;

use Box\Spout\Reader\CSV\SheetIterator as SpoutSheetIterator;

class SheetIterator extends SpoutSheetIterator
{
    /**
     * @param resource $filePointer
     * @param \Box\Spout\Reader\CSV\ReaderOptions $options
     * @param \Box\Spout\Common\Helper\GlobalFunctionsHelper $globalFunctionsHelper
     */
    public function __construct($filePointer, $options, $globalFunctionsHelper)
    {
        $this->sheet = new Sheet($filePointer, $options, $globalFunctionsHelper);
    }
}
