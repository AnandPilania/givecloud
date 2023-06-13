<?php

namespace Ds\Repositories;

use Ds\Models\Membership;
use Illuminate\Database\Eloquent\Builder;

class MembershipRepository
{
    /** @var \Ds\Models\Membership */
    protected $model;

    public function __construct(Membership $model)
    {
        $this->model = $model;
    }

    public function queryCreatedOrUpdatedAfterPurchase(): Builder
    {
        $siteCreationDate = site()->created_at;
        if ($siteCreationDate) {
            return $this->model->newQuery()
                ->whereDate('created_at', '>=', $siteCreationDate->toDateTimeFormat())
                ->OrWhereDate('updated_at', '>=', $siteCreationDate->toDateTimeFormat());
        }

        // Return Builder with no results.
        return $this->model->newQuery()
            ->whereNull('created_at');
    }
}
