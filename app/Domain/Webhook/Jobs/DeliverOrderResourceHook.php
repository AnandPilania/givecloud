<?php

namespace Ds\Domain\Webhook\Jobs;

use Ds\Domain\Webhook\Services\HookService;
use Ds\Http\Resources\OrderResource;
use Ds\Jobs\Job;
use Ds\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeliverOrderResourceHook extends Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    /** @var string */
    protected $eventName;

    /** @var \Ds\Models\Order */
    protected $order;

    public function __construct(string $eventName, Order $order)
    {
        $this->eventName = $eventName;
        $this->order = $order;
    }

    public function handle(HookService $hookService): void
    {
        $hookService->makeDeliveries($this->eventName, new OrderResource($this->order));
    }
}
