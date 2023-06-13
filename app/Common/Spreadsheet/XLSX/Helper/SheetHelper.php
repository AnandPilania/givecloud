<?php

namespace Ds\Common\Spreadsheet\XLSX\Helper;

use Box\Spout\Reader\XLSX\Helper\SheetHelper as SpoutSheetHelper;
use Ds\Common\Spreadsheet\XLSX\Sheet;

class SheetHelper extends SpoutSheetHelper
{
    /**
     * Returns an instance of a sheet, given the XML node describing the sheet - from "workbook.xml".
     * We can find the XML file path describing the sheet inside "workbook.xml.res", by mapping with the sheet ID
     * ("r:id" in "workbook.xml", "Id" in "workbook.xml.res").
     *
     * @param \Box\Spout\Reader\Wrapper\XMLReader $xmlReaderOnSheetNode XML Reader instance, pointing on the node describing the sheet, as defined in "workbook.xml"
     * @param int $sheetIndexZeroBased Index of the sheet, based on order of appearance in the workbook (zero-based)
     * @param bool $isSheetActive Whether this sheet was defined as active
     * @return \Box\Spout\Reader\XLSX\Sheet Sheet instance
     */
    protected function getSheetFromSheetXMLNode($xmlReaderOnSheetNode, $sheetIndexZeroBased, $isSheetActive)
    {
        $sheetId = $xmlReaderOnSheetNode->getAttribute(self::XML_ATTRIBUTE_R_ID);
        $escapedSheetName = $xmlReaderOnSheetNode->getAttribute(self::XML_ATTRIBUTE_NAME);

        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $escaper = \Box\Spout\Common\Escaper\XLSX::getInstance();
        $sheetName = $escaper->unescape($escapedSheetName);

        $sheetDataXMLFilePath = $this->getSheetDataXMLFilePathForSheetId($sheetId);

        return new Sheet(
            $this->filePath,
            $sheetDataXMLFilePath,
            $sheetIndexZeroBased,
            $sheetName,
            $isSheetActive,
            $this->options,
            $this->sharedStringsHelper
        );
    }
}
