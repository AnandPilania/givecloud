<?php

namespace Ds\Domain\Webhook\Repositories;

use Ds\Models\Hook;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class HookRepository
{
    /** @var \Ds\Models\Hook */
    protected $model;

    public function __construct(Hook $model)
    {
        $this->model = $model;
    }

    public function byActiveAndEvent(string $eventName): Builder
    {
        return $this->model->newQuery()
            ->active()
            ->whereHas('events', function ($query) use ($eventName) {
                return $query->where('name', $eventName);
            });
    }

    public function getActiveByEvent(string $eventName): Collection
    {
        return $this
            ->byActiveAndEvent($eventName)
            ->get();
    }
}
