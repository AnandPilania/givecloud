<?php

namespace Ds\Domain\HotGlue\Listeners\Salesforce;

use Ds\Domain\HotGlue\Listeners\AbstractHandler;
use Ds\Domain\HotGlue\Targets\AbstractTarget;
use Ds\Domain\HotGlue\Targets\SalesforceTarget;
use Ds\Domain\HotGlue\Transformers\Salesforce\AccountTransformer;
use Ds\Domain\HotGlue\Transformers\Salesforce\ContributionTransformer;
use Ds\Events\Event;
use League\Fractal\Resource\Collection;

class OrderPaid extends AbstractHandler
{
    public function target(): AbstractTarget
    {
        return app(SalesforceTarget::class);
    }

    public function state(Event $event): array
    {
        $accounts = [];

        $order = new Collection([$event->order], new ContributionTransformer, 'Deals');
        $orders = app('fractal')->createArray($order);

        if ($event->order->member) {
            $account = new Collection([$event->order->member], new AccountTransformer, 'Contacts');
            $accounts = app('fractal')->createArray($account);
        }

        return array_merge(
            $accounts,
            $orders,
        );
    }

    public function shouldQueue(): bool
    {
        return sys_get('salesforce_opportunity_external_id')
            && parent::shouldQueue();
    }
}
