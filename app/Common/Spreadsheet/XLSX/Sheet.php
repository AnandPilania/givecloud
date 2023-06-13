<?php

namespace Ds\Common\Spreadsheet\XLSX;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\Wrapper\XMLReader;
use Box\Spout\Reader\XLSX\RowIterator;
use Box\Spout\Reader\XLSX\Sheet as SpoutSheet;
use Ds\Common\Spreadsheet\SheetInterface;

class Sheet extends SpoutSheet implements SheetInterface
{
    /** @var string Path of the XLSX file being read */
    protected $filePath;

    /** @var string Path of the sheet data XML file as in [Content_Types].xml */
    protected $sheetDataXMLFilePath;

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param string $sheetDataXMLFilePath Path of the sheet data XML file as in [Content_Types].xml
     * @param int $sheetIndex Index of the sheet, based on order in the workbook (zero-based)
     * @param string $sheetName Name of the sheet
     * @param bool $isSheetActive Whether the sheet was defined as active
     * @param \Box\Spout\Reader\XLSX\ReaderOptions $options Reader's current options
     * @param \Box\Spout\Reader\XLSX\Helper\SharedStringsHelper $sharedStringsHelper Helper to work with shared strings
     */
    public function __construct($filePath, $sheetDataXMLFilePath, $sheetIndex, $sheetName, $isSheetActive, $options, $sharedStringsHelper)
    {
        $this->filePath = $filePath;
        $this->sheetDataXMLFilePath = $this->normalizeSheetDataXMLFilePath($sheetDataXMLFilePath);

        parent::__construct($filePath, $sheetDataXMLFilePath, $sheetIndex, $sheetName, $isSheetActive, $options, $sharedStringsHelper);
    }

    /**
     * @param string $sheetDataXMLFilePath Path of the sheet data XML file as in [Content_Types].xml
     * @return string path of the XML file containing the sheet data,
     *                without the leading slash
     */
    protected function normalizeSheetDataXMLFilePath($sheetDataXMLFilePath)
    {
        return ltrim($sheetDataXMLFilePath, '/');
    }

    /**
     * Count the rows without extracting any data.
     *
     * @return int
     */
    public function getTotalRows()
    {
        $xmlReader = new XMLReader;

        $realPathURI = $xmlReader->getRealPathURIForFileInZip($this->filePath, $this->sheetDataXMLFilePath);

        if ($xmlReader->openFileInZip($this->filePath, $this->sheetDataXMLFilePath) === false) {
            throw new IOException("Could not open \"{$this->sheetDataXMLFilePath}\".");
        }

        $xmlReader->setParserProperty(XMLReader::DEFAULTATTRS, true);

        $count = 0;
        while ($xmlReader->read()) {
            if ($xmlReader->name === RowIterator::XML_NODE_ROW && $xmlReader->nodeType === XMLReader::ELEMENT) {
                $count++;
            }
        }

        return $count;
    }
}
