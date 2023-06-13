<?php

namespace Ds\Repositories;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\AccountType;

class AccountTypeRepository
{
    /** @var \Ds\Models\AccountType */
    protected $model;

    public function __construct(AccountType $model)
    {
        $this->model = $model;
    }

    public function findIndividual(): ?AccountType
    {
        return $this->model->newQuery()
            ->where('is_organization', '=', false)
            ->first();
    }

    public function getOnWebAccountTypeDrops(): array
    {
        if (! sys_get('allow_account_types_on_web')) {
            return [];
        }

        return Drop::resolveData(
            AccountType::query()
                ->onWeb()
                ->orderBy('is_default', 'desc')
                ->orderBy('sequence', 'asc')
                ->get()
        );
    }
}
