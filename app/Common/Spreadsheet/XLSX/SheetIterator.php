<?php

namespace Ds\Common\Spreadsheet\XLSX;

use Box\Spout\Reader\Exception\NoSheetsFoundException;
use Box\Spout\Reader\XLSX\SheetIterator as SpoutSheetIterator;
use Ds\Common\Spreadsheet\XLSX\Helper\SheetHelper;

class SheetIterator extends SpoutSheetIterator
{
    /**
     * @param string $filePath Path of the file to be read
     * @param \Box\Spout\Reader\XLSX\ReaderOptions $options Reader's current options
     * @param \Box\Spout\Reader\XLSX\Helper\SharedStringsHelper $sharedStringsHelper
     * @param \Box\Spout\Common\Helper\GlobalFunctionsHelper $globalFunctionsHelper
     *
     * @throws \Box\Spout\Reader\Exception\NoSheetsFoundException If there are no sheets in the file
     */
    public function __construct($filePath, $options, $sharedStringsHelper, $globalFunctionsHelper)
    {
        // Fetch all available sheets
        $sheetHelper = new SheetHelper($filePath, $options, $sharedStringsHelper, $globalFunctionsHelper);
        $this->sheets = $sheetHelper->getSheets();

        if (count($this->sheets) === 0) {
            throw new NoSheetsFoundException('The file must contain at least one sheet.');
        }
    }
}
