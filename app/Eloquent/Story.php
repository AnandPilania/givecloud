<?php

namespace Ds\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\Concerns\InteractsWithTime;
use Illuminate\Support\Traits\Conditionable;

abstract class Story
{
    use Conditionable;
    use InteractsWithTime;

    /** @var int */
    protected $count = 1;

    /**
     * @return static
     */
    public static function factory(...$parameters): self
    {
        return new static(...$parameters);
    }

    /**
     * @return static
     */
    public function count(int $count = 1): self
    {
        $this->count = $count;

        return $this;
    }

    abstract protected function execute(): Model;

    /**
     * @return array|\Illuminate\Database\Eloquent\Model
     */
    public function create()
    {
        if ($this->count === 1) {
            return $this->execute();
        }

        $data = [];

        for ($i = 0; $i < $this->count; $i++) {
            $data[] = $this->execute();
        }

        return $data;
    }
}
