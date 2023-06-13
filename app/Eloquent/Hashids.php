<?php

namespace Ds\Eloquent;

use Illuminate\Support\Arr;

/** @phan-file-suppress PhanUndeclaredMethod */
trait Hashids
{
    /**
     * Get a Hashids instance.
     *
     * @return \Hashids\Hashids
     */
    protected static function getHashids()
    {
        return app('hashids');
    }

    /**
     * Returns a decoded hashid.
     *
     * @param string $hashid
     * @return int
     */
    public static function decodeHashid($hashid)
    {
        $result = static::getHashids()->decode($hashid);
        if (count($result) === 1) {
            return (int) Arr::get($result, 0);
        }

        return (int) $hashid;
    }

    /**
     * The hashid for the model id.
     *
     * @return string
     */
    public function getHashidAttribute()
    {
        return static::getHashids()->encode($this->id);
    }

    /**
     * Scope converting hashid where statements into primary
     * key based where statements.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $hashid
     */
    public function scopeHashid($query, $hashid)
    {
        $query->where($this->getQualifiedKeyName(), static::decodeHashid($hashid));
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return parent::resolveRouteBinding(static::decodeHashid($value), $field);
    }

    /**
     * Retrieve the child model for a bound value.
     *
     * @param string $childType
     * @param mixed $value
     * @param string|null $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        return parent::resolveChildRouteBinding($childType, static::decodeHashid($value), $field);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (in_array($method, ['find', 'findMany', 'findOrFail', 'findOrNew'])) {
            // Decrypt the primary key array of primary key values
            if (is_string($parameters[0])) {
                $parameters[0] = static::decodeHashid($parameters[0]);
            }

            // Decrypt primary key value
            elseif (is_array($parameters[0])) {
                foreach ($parameters[0] as &$value) {
                    $value = static::decodeHashid($value);
                }
            }
        }

        return parent::__callStatic($method, $parameters);
    }
}
