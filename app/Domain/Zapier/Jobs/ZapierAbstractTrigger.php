<?php

namespace Ds\Domain\Zapier\Jobs;

use Ds\Repositories\ResthookSubscriptionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class ZapierAbstractTrigger implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function pushToZapier(
        string $eventName,
        JsonResource $resourceToSend,
        ResthookSubscriptionRepository $resthookSubscriptionRepository
    ): void {
        $resthookSubscriptionRepository
            ->getByEvent($eventName)
            ->each(function ($eventResthookSubscription) use ($resourceToSend) {
                // Pass only the key and not the ResthookSubscription model,
                // in case the record has been deleted before running the job.
                PostToZapier::dispatch($resourceToSend, $eventResthookSubscription->getKey());
            });
    }
}
