<?php

namespace Ds\Domain\Webhook\Jobs;

use Ds\Domain\Webhook\Services\HookService;
use Ds\Domain\Webhook\Transformers\MemberTransformer;
use Ds\Jobs\Job;
use Ds\Models\Member;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Fractal\Resource\Collection;

class DeliverAccountHook extends Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    /** @var string */
    protected $eventName;

    /** @var \Ds\Models\Member */
    protected $member;

    public function __construct(string $eventName, Member $member)
    {
        $this->eventName = $eventName;
        $this->member = $member;
    }

    public function handle(HookService $hookService): void
    {
        $members = new Collection([$this->member], new MemberTransformer, 'supporters');

        $hookService->makeDeliveries($this->eventName, $members);
    }
}
