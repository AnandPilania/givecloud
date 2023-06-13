<?php

namespace Ds\Models\Observers;

use Ds\Models\AccountType;

class AccountTypeObserver
{
    /**
     * Response to the deleted event.
     *
     * @param \Ds\Models\AccountType $model
     * @return bool|void
     */
    public function deleting(AccountType $model)
    {
        // do not allow deleting account types that are protected
        if ($model->is_protected) {
            return false;
        }
    }
}
