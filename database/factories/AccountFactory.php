<?php

namespace Database\Factories;

use Ds\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends MemberFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;
}
