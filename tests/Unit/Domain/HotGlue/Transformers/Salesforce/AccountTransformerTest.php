<?php

namespace Tests\Unit\Domain\HotGlue\Transformers\Salesforce;

use Ds\Domain\HotGlue\Transformers\Salesforce\AccountTransformer;
use Ds\Models\Account;
use Tests\TestCase;

/**
 * @group hotglue
 */
class AccountTransformerTest extends TestCase
{
    public function testTransformerReturnsArrayOfValues(): void
    {
        $account = Account::factory()->createQuietly();

        $data = $this->app->make(AccountTransformer::class)->transform($account);

        $this->assertIsArray($data);

        $this->assertSame($account->display_name, $data['name']);
        $this->assertSame($account->first_name, $data['first_name']);
        $this->assertSame($account->last_name, $data['last_name']);
        $this->assertSame($account->email, $data['email']);
        $this->assertSame($account->bill_organization_name, $data['company_name']);
    }
}
