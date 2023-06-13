<?php

namespace Ds\Illuminate\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory as EloquentHasFactory;
use Illuminate\Support\Str;

trait HasFactory
{
    use EloquentHasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public static function newFactory()
    {
        $modelName = get_called_class();
        $modelName = Str::startsWith($modelName, 'Ds\\Domain\\')
            ? preg_replace('~^Ds\\\\(Domain\\\\.*?)(?:\\\\Models(\\\\[^\\\\]+)|([^\\\\]+))$~', 'Database\\\\Factories\\\\$1$2Factory', $modelName)
            : Factory::resolveFactoryName($modelName);

        return $modelName::new();
    }
}
