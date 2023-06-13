<?php

namespace Ds\Models\Observers;

use Ds\Events\AccountCreated;
use Ds\Events\AccountWasUpdated;
use Ds\Models\Account;
use Ds\Models\Member;

class MemberObserver
{
    /**
     * Response to the creating event.
     *
     * @param \Ds\Models\Member $model
     * @return void
     */
    public function creating(Member $model)
    {
        // default account type
        if (! $model->account_type_id) {
            $model->account_type_id = data_get(\Ds\Models\AccountType::default()->first(), 'id', 1);
        }
        $model->attachReferrer();
    }

    /**
     * Response to the created event.
     *
     * @param \Ds\Models\Member $model
     * @return void
     */
    public function created(Member $model)
    {
        event(new AccountCreated(Account::find($model->id)));
    }

    /**
     * Response to the saving event (create/update)
     *
     * @param \Ds\Models\Member $model
     * @return void
     */
    public function saving(Member $model)
    {
        $model->setDisplayName();
    }

    /**
     * Response to the updated event.
     */
    public function updated(Member $model): void
    {
        event(new AccountWasUpdated($model));
    }
}
