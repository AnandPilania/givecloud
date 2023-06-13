<?php

namespace Ds\Domain\Salesforce\Database;

class Repository
{
    public function find($object, $ids)
    {
        return $object::find($ids);
    }

    public function findByLocalKeys($object, $ids)
    {
        return $object::whereIn((new $object)->externalKey, $ids)->get();
    }

    public function firstOrCreate($object, array $attributes = [], array $values = [])
    {
        return $object::firstOrCreate($attributes, $values);
    }

    public function upsertRecords($object, array $records = [])
    {
        return $object::upsertRecords($records);
    }
}
