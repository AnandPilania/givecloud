<?php

namespace Ds\Repositories;

use Ds\Models\ResthookSubscription;
use Illuminate\Database\Eloquent\Collection;

class ResthookSubscriptionRepository extends Repository
{
    public function __construct(ResthookSubscription $model)
    {
        $this->model = $model;
    }

    public function getByEvent(string $event): Collection
    {
        return $this->query()
            ->where('event', '=', $event)
            ->get();
    }

    public function getByTarget(string $targetUrl): ?ResthookSubscription
    {
        return $this->query()
            ->where('target_url', '=', $targetUrl)
            ->first();
    }
}
