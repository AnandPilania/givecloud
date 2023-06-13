<?php

namespace Ds\Domain\Zapier\Services;

use Ds\Models\Transaction;
use Ds\Models\User;
use Ds\Repositories\TransactionRepository;

class TransactionService
{
    public function getRandomTransactionOrFakeIt(User $apiUser): Transaction
    {
        return app(TransactionRepository::class)->getRandomSucceededTransaction() ?: $this->makeForUser($apiUser);
    }

    protected function makeForUser(User $apiUser): Transaction
    {
        /** @var \Ds\Models\Transaction */
        $fake = Transaction::factory()->make();

        $fake->createdBy = $apiUser;

        return $fake;
    }
}
