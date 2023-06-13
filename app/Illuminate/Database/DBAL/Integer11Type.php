<?php

namespace Ds\Illuminate\Database\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

class Integer11Type extends IntegerType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return preg_replace('/^INT/', 'INT(11)', $platform->getIntegerTypeDeclarationSQL($column));
    }

    public function getName()
    {
        return 'integer11';
    }
}
