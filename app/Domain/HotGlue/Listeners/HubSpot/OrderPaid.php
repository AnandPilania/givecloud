<?php

namespace Ds\Domain\HotGlue\Listeners\HubSpot;

use Ds\Domain\HotGlue\Listeners\AbstractHandler;
use Ds\Domain\HotGlue\Targets\AbstractTarget;
use Ds\Domain\HotGlue\Targets\HubSpotTarget;
use Ds\Domain\HotGlue\Transformers\ContactTransformer;
use Ds\Domain\HotGlue\Transformers\DealTransformer;
use Ds\Events\Event;
use League\Fractal\Resource\Collection;

class OrderPaid extends AbstractHandler
{
    public function target(): AbstractTarget
    {
        return app(HubSpotTarget::class);
    }

    public function state(Event $event): array
    {
        $accounts = [];

        $order = new Collection([$event->order], new DealTransformer, 'Deals');
        $orders = app('fractal')->createArray($order);

        data_set($orders, 'Deals.0.status', 'closedwon');

        if ($event->order->member) {
            $account = new Collection([$event->order->member], new ContactTransformer, 'Contacts');
            $accounts = app('fractal')->createArray($account);
        }

        return array_merge(
            $accounts,
            $orders,
        );
    }
}
