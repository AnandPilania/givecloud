<?php

namespace Tests\Unit\Domain\HotGlue\Transformers\Mailchimp;

use Ds\Domain\HotGlue\Transformers\Mailchimp\AccountTransformer;
use Ds\Models\Account;
use Tests\TestCase;

/**
 * @group hotglue
 */
class AccountTransformerTest extends TestCase
{
    public function testTransformerReturnsArrayOfValues(): void
    {
        $value = (bool) random_int(0, 1);

        $account = Account::factory()->createQuietly();
        $account->email_opt_in = $value;

        $data = $this->app->make(AccountTransformer::class)->transform($account);

        $this->assertIsArray($data);

        $this->assertSame($account->display_name, $data['name']);
        $this->assertSame($account->email, $data['email']);
        $this->assertSame($value ? 'subscribed' : 'unsubscribed', $data['subscribe_status']);
    }
}
