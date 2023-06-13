<?php

namespace Ds\Listeners\Product;

use Ds\Events\PledgableAmountRollback;
use Ds\Events\ProductWasRefunded;
use Ds\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class RollbackPledgableAmounts implements ShouldQueue
{
    public function handle(ProductWasRefunded $event): void
    {
        foreach ($this->getCampaignsQuery($event->product)->get() as $campaign) {
            event(new PledgableAmountRollback($campaign, $event->item));
        }
    }

    public function shouldQueue(ProductWasRefunded $event): bool
    {
        return feature('pledges') && $this->getCampaignsQuery($event->product)->exists();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Ds\Models\PledgeCampaign>
     */
    private function getCampaignsQuery(Product $product): MorphToMany
    {
        return $product->pledgeCampaigns()->active();
    }
}
