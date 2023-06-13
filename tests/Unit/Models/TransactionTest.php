<?php

namespace Tests\Unit\Models;

use Ds\Models\Transaction;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    public function testPrefixedIdIsPrefixedAndHashed(): void
    {
        $transaction = Transaction::factory()->create();

        $hash = $this->app->make('hashids')->encode($transaction->id);

        $this->assertSame('txn_' . $hash, $transaction->prefixed_id);
    }
}
