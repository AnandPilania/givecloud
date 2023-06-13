<?php

namespace Tests\Unit\Domain\HotGlue\Transformers;

use Ds\Domain\HotGlue\Transformers\ContactTransformer;
use Ds\Models\Account;
use Tests\TestCase;

/**
 * @group hotglue
 */
class ContactTransformerTest extends TestCase
{
    public function testTransformerReturnsArrayOfValues(): void
    {
        $value = (bool) random_int(0, 1);

        $account = Account::factory()->createQuietly();
        $account->email_opt_in = $value;

        $data = $this->app->make(ContactTransformer::class)->transform($account);

        $this->assertIsArray($data);

        $this->assertSame($account->display_name, $data['name']);
        $this->assertSame($account->first_name, $data['first_name']);
        $this->assertSame($account->last_name, $data['last_name']);
        $this->assertSame($account->email, $data['email']);
        $this->assertSame($account->bill_organization_name, $data['company_name']);
        $this->assertSame($account->bill_address_01, $data['addresses'][0]['line1']);
        $this->assertSame($account->bill_address_02, $data['addresses'][0]['line2']);
        $this->assertSame($account->bill_city, $data['addresses'][0]['city']);
        $this->assertSame($account->bill_state, $data['addresses'][0]['state']);
        $this->assertSame($account->bill_country, $data['addresses'][0]['country']);
        $this->assertSame($account->bill_zip, $data['addresses'][0]['postal_code']);
        $this->assertSame($account->bill_phone, $data['phone_numbers'][0]['number']);
    }
}
