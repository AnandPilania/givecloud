<?php

namespace Ds\Domain\Zapier\Jobs;

use Ds\Domain\Zapier\Enums\Events;
use Ds\Http\Resources\OrderResource;
use Ds\Models\Order;
use Ds\Repositories\ResthookSubscriptionRepository;

class OrderPaidTrigger extends ZapierAbstractTrigger
{
    /** @var \Ds\Models\Order */
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(ResthookSubscriptionRepository $resthookSubscriptionRepository): void
    {
        $this->pushToZapier(Events::CONTRIBUTION_PAID, OrderResource::collection([$this->order]), $resthookSubscriptionRepository);
    }
}
