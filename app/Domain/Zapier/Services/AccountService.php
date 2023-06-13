<?php

namespace Ds\Domain\Zapier\Services;

use Ds\Models\Member;
use Ds\Models\User;
use Ds\Repositories\AccountRepository;
use Ds\Repositories\AccountTypeRepository;

class AccountService
{
    /** @var \Ds\Repositories\AccountRepository */
    protected $accountRepository;

    /** @var \Ds\Repositories\AccountTypeRepository */
    protected $accountTypeRepository;

    public function __construct(AccountRepository $accountRepository, AccountTypeRepository $accountTypeRepository)
    {
        $this->accountRepository = $accountRepository;
        $this->accountTypeRepository = $accountTypeRepository;
    }

    public function getRandomAccountOrFakeIt(User $apiUser): Member
    {
        return $this->accountRepository->getRandomAccount() ?: $this->makeUser($apiUser);
    }

    protected function makeUser(User $apiUser): Member
    {
        /** @var \Ds\Models\Member */
        $fakeAccount = Member::factory()->individual()->make();

        $fakeAccount->accountType = $this->accountTypeRepository->findIndividual();
        $fakeAccount->createdBy = $apiUser;

        return $fakeAccount;
    }
}
