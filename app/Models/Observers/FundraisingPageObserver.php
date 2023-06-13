<?php

namespace Ds\Models\Observers;

use Ds\Enums\FundraisingPageType;
use Ds\Models\FundraisingPage;
use Ds\Services\SupporterVerificationStatusService;

class FundraisingPageObserver
{
    public function created(FundraisingPage $fundraisingPage): void
    {
        app(SupporterVerificationStatusService::class)->updateSupporterStatus($fundraisingPage->memberOrganizer);
    }

    public function creating(FundraisingPage $model): void
    {
        // if there is no url defined,
        // set it
        if (empty($model->url) && $model->type === FundraisingPageType::WEBSITE) {
            $model->url = FundraisingPage::createUniqueUrl($model->title, $model->id);
        }
    }

    public function saving(FundraisingPage $fundraisingPage): void
    {
        if ($fundraisingPage->team_name && empty($fundraisingPage->team_join_code)) {
            $fundraisingPage->regenerateTeamJoinCode();
        }
    }

    public function updated(FundraisingPage $fundraisingPage): void
    {
        app(SupporterVerificationStatusService::class)->updateSupporterStatus($fundraisingPage->memberOrganizer);
    }
}
