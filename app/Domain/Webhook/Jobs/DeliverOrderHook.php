<?php

namespace Ds\Domain\Webhook\Jobs;

use Ds\Domain\Webhook\Services\HookService;
use Ds\Domain\Webhook\Transformers\OrderTransformer;
use Ds\Jobs\Job;
use Ds\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Fractal\Resource\Collection;

class DeliverOrderHook extends Job implements ShouldQueue
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
        $orders = new Collection([$this->order], new OrderTransformer, 'contributions');

        $hookService->makeDeliveries($this->eventName, $orders);
    }
}
