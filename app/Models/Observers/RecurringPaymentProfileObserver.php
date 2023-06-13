<?php

namespace Ds\Models\Observers;

use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Domain\Sponsorship\Services\SponsorCountService;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Models\RecurringPaymentProfile;

class RecurringPaymentProfileObserver
{
    /**
     * Response to the updated event.
     *
     * @param \Ds\Models\RecurringPaymentProfile $model
     * @return void
     */
    public function updated(RecurringPaymentProfile $model)
    {
        // if the status of the recurring payment profile has changed
        if ($model->isDirty('status')) {
            // if the status of the recurring payment profie is set to
            // 'active' and there is a sponsorship accosiated with
            // it, make sure the sponsors' ended_at date and
            // ended_reason are removed
            if ($model->status == RecurringPaymentProfileStatus::ACTIVE && $model->sponsorship && $model->sponsor && $model->sponsor->ended_at) {
                $model->sponsor->ended_at = null;
                $model->sponsor->ended_reason = null;
                $model->sponsor->save();

                $this->updateSponsorshipCount($model->sponsor);
            }

            // if the status of the recurring payment profie is set to
            // 'suspended' or 'ended' and there is a sponsorship
            // accosiated with it, make sure the sponsors' ended_at
            // date is set
            elseif (($model->status == RecurringPaymentProfileStatus::SUSPENDED || $model->status == RecurringPaymentProfileStatus::CANCELLED) && ($model->sponsorship && ! $model->sponsor->ended_at)) {
                // end the sponsorship according to the settings
                // configured by the client
                if ((sys_get('sponsorship_end_on_rpp_suspend') && $model->status == RecurringPaymentProfileStatus::SUSPENDED)
                     or (sys_get('sponsorship_end_on_rpp_cancel') && $model->status == RecurringPaymentProfileStatus::CANCELLED)) {
                    $model->sponsor->ended_at = \Carbon\Carbon::now();
                    $model->sponsor->ended_reason = 'Recurring payment ' . strtolower($model->status);
                    $model->sponsor->save();

                    $this->updateSponsorshipCount($model->sponsor);
                }
            }
        }
    }

    private function updateSponsorshipCount(Sponsor $sponsor): void
    {
        app(SponsorCountService::class)->update($sponsor->sponsorship);
    }
}
