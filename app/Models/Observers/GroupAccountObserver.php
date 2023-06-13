<?php

namespace Ds\Models\Observers;

use Ds\Models\GroupAccount;
use Ds\Models\GroupAccountTimespan;

class GroupAccountObserver
{
    /**
     * Handle the GroupAccount "created" event.
     *
     * @param \Ds\Models\GroupAccount $groupAccount
     * @return void
     */
    public function created(GroupAccount $groupAccount)
    {
        $this->regenerateGroupAccountTimespan($groupAccount);
    }

    /**
     * Handle the GroupAccount "updated" event.
     *
     * @param \Ds\Models\GroupAccount $groupAccount
     * @return void
     */
    public function updated(GroupAccount $groupAccount)
    {
        // if the group was changed then we need to regenerate the previous
        // group account timespan as well to prevent it from potentially being orphaned
        if ($groupAccount->isDirty('group_id')) {
            $this->aggregateGroupAccountTimespan(
                $groupAccount->getOriginal('group_id'),
                $groupAccount->account_id,
            );
        }

        $this->regenerateGroupAccountTimespan($groupAccount);
    }

    /**
     * Handle the GroupAccount "deleted" event.
     *
     * @param \Ds\Models\GroupAccount $groupAccount
     * @return void
     */
    public function deleted(GroupAccount $groupAccount)
    {
        $this->regenerateGroupAccountTimespan($groupAccount);
    }

    /**
     * Handle the GroupAccount "restored" event.
     *
     * @param \Ds\Models\GroupAccount $groupAccount
     * @return void
     */
    public function restored(GroupAccount $groupAccount)
    {
        $this->regenerateGroupAccountTimespan($groupAccount);
    }

    /**
     * Handle the GroupAccount "force deleted" event.
     *
     * @param \Ds\Models\GroupAccount $groupAccount
     * @return void
     */
    public function forceDeleted(GroupAccount $groupAccount)
    {
        $this->regenerateGroupAccountTimespan($groupAccount);
    }

    private function regenerateGroupAccountTimespan(GroupAccount $groupAccount): void
    {
        $this->aggregateGroupAccountTimespan($groupAccount->group_id, $groupAccount->account_id);

        // a refresh is necessary to ensure that the group account timespan
        // relation added to the group account is now reflected in the model
        $groupAccount->refresh();
    }

    private function aggregateGroupAccountTimespan(?int $groupId, ?int $accountId): void
    {
        if (empty($groupId) || empty($accountId)) {
            return;
        }

        GroupAccountTimespan::aggregate($groupId, $accountId);
    }
}
