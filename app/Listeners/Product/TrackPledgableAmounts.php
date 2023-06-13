<?php

namespace Ds\Listeners\Product;

use Ds\Events\PledgableAmountCommitted;
use Ds\Events\ProductWasPurchased;
use Ds\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class TrackPledgableAmounts implements ShouldQueue
{
    public function handle(ProductWasPurchased $event): void
    {
        foreach ($this->getCampaignsQuery($event->product)->get() as $campaign) {
            event(new PledgableAmountCommitted($campaign, $event->item));
        }
    }

    public function shouldQueue(ProductWasPurchased $event): bool
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
