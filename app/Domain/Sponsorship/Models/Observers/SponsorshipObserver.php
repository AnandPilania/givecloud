<?php

namespace Ds\Domain\Sponsorship\Models\Observers;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Sponsorship\Models\Sponsorship;

class SponsorshipObserver
{
    /**
     * Response to the saving event.
     *
     * @param \Ds\Domain\Sponsorship\Models\Sponsorship $model
     * @return void
     */
    public function updating(Sponsorship $model)
    {
        if ($model->isDirty('is_sponsored_auto') && $model->is_sponsored_auto) {
            // update status based on sponsor_count
            $model->updateIsSponsored(false);

            // TODO: refactor for one child

            // We need to join the sponsor tables for each of the child sites
            // and then get a count from that

            // $model->sponsor_count = $model->sponsors()->active()->count();
        }
    }

    /**
     * Response to the deleting event.
     *
     * @param \Ds\Domain\Sponsorship\Models\Sponsorship $model
     * @return void
     */
    public function deleting(Sponsorship $model)
    {
        if ($model->sponsor_count > 0) {
            throw new MessageException('Cannot delete sponsorship. It has ' . $model->sponsor_count . ' active sponsorships associated with it. ');
        }
    }
}
