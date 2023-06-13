<?php

namespace Ds\Common\Spreadsheet\XLSX;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\XLSX\Helper\SharedStringsHelper;
use Box\Spout\Reader\XLSX\Reader as SpoutReader;

class Reader extends SpoutReader
{
    /**
     * Opens the file at the given file path to make it ready to be read.
     * It also parses the sharedStrings.xml file to get all the shared strings available in memory
     * and fetches all the available sheets.
     *
     * @param string $filePath Path of the file to be read
     * @return void
     *
     * @throws \Box\Spout\Common\Exception\IOException If the file at the given path or its content cannot be read
     * @throws \Box\Spout\Reader\Exception\NoSheetsFoundException If there are no sheets in the file
     */
    protected function openReader($filePath)
    {
        $this->zip = new \ZipArchive();

        if ($this->zip->open($filePath) === true) {
            $this->sharedStringsHelper = new SharedStringsHelper($filePath, $this->getOptions()->getTempFolder());

            if ($this->sharedStringsHelper->hasSharedStrings()) {
                // Extracts all the strings from the sheets for easy access in the future
                $this->sharedStringsHelper->extractSharedStrings();
            }

            $this->sheetIterator = new SheetIterator($filePath, $this->getOptions(), $this->sharedStringsHelper, $this->globalFunctionsHelper);
        } else {
            throw new IOException("Could not open $filePath for reading.");
        }
    }
}
