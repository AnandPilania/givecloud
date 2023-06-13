<?php

namespace Ds\Common\Spreadsheet;

use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderInterface;

class Manager
{
    /**
     * This creates an instance of the appropriate reader, given the type of the file to be read
     *
     * @api
     *
     * @param string $readerType Type of the reader to instantiate
     * @return \Box\Spout\Reader\ReaderInterface
     *
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     */
    public function create($readerType): ReaderInterface
    {
        $reader = null;

        switch ($readerType) {
            case Type::CSV:
                $reader = new CSV\Reader;
                break;
            case Type::XLSX:
                $reader = new XLSX\Reader;
                break;
            default:
                throw new UnsupportedTypeException("No readers supporting the given type: $readerType");
        }

        $reader->setGlobalFunctionsHelper(new GlobalFunctionsHelper);

        return $reader;
    }
}
