<?php

namespace Ds\Common\Spreadsheet\CSV;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\CSV\Reader as SpoutReader;

class Reader extends SpoutReader
{
    /**
     * Opens the file at the given path to make it ready to be read.
     * If setEncoding() was not called, it assumes that the file is encoded in UTF-8.
     *
     * @param string $filePath Path of the CSV file to be read
     * @return void
     *
     * @throws \Box\Spout\Common\Exception\IOException
     */
    protected function openReader($filePath)
    {
        $this->originalAutoDetectLineEndings = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');

        $this->filePointer = $this->globalFunctionsHelper->fopen($filePath, 'r');
        if (! $this->filePointer) {
            throw new IOException("Could not open file $filePath for reading.");
        }

        $this->sheetIterator = new SheetIterator(
            $this->filePointer,
            $this->getOptions(),
            $this->globalFunctionsHelper
        );
    }
}
