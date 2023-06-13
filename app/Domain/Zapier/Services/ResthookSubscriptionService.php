<?php

namespace Ds\Domain\Zapier\Services;

use Ds\Models\ResthookSubscription;
use Ds\Repositories\ResthookSubscriptionRepository;
use Illuminate\Database\Eloquent\Collection;

class ResthookSubscriptionService
{
    /** @var \Ds\Models\ResthookSubscription */
    protected $resthookSubscription;

    /** @var \Ds\Repositories\ResthookSubscriptionRepository */
    protected $resthookSubscriptionRepository;

    public function __construct(
        ResthookSubscription $resthookSubscription,
        ResthookSubscriptionRepository $resthookSubscriptionRepository
    ) {
        $this->resthookSubscription = $resthookSubscription;
        $this->resthookSubscriptionRepository = $resthookSubscriptionRepository;
    }

    public function index(): Collection
    {
        return $this->resthookSubscriptionRepository->get();
    }

    public function store(string $event, string $targetUrl, int $userId): ?ResthookSubscription
    {
        $resthookSubscription = $this->resthookSubscription->newInstance([
            'event' => $event,
            'target_url' => $targetUrl,
            'user_id' => $userId,
        ]);

        return $resthookSubscription->save() ? $resthookSubscription : null;
    }

    public function update(
        ResthookSubscription $resthookSubscription,
        string $event,
        string $targetUrl,
        int $userId
    ): bool {
        $resthookSubscription->fill([
            'event' => $event,
            'target_url' => $targetUrl,
            'user_id' => $userId,
        ]);

        return $resthookSubscription->save();
    }
}
