<?php

namespace Ds\Domain\Webhook\Jobs;

use Ds\Domain\Webhook\Services\HookService;
use Ds\Http\Resources\ContributionResource;
use Ds\Jobs\Job;
use Ds\Models\Contribution;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeliverContributionResourceHook extends Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    /** @var string */
    protected $eventName;

    /** @var \Ds\Models\Contribution */
    protected $contribution;

    public function __construct(string $eventName, Contribution $contribution)
    {
        $this->eventName = $eventName;
        $this->contribution = $contribution;
    }

    public function handle(HookService $hookService): void
    {
        $hookService->makeDeliveries($this->eventName, ContributionResource::make($this->contribution));
    }
}
