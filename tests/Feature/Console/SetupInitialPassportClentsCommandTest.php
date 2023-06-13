<?php

namespace Tests\Feature\Console;

use Ds\Models\Passport\Client;
use Laravel\Passport\Passport;
use Tests\TestCase;

class SetupInitialPassportClentsCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Passport::client()->truncate();
    }

    public function tearDown(): void
    {
        Passport::client()->truncate();

        parent::tearDown();
    }

    public function testClientsAreCreated()
    {
        $this->artisan('passport:setup-initial-clients')
            ->expectsOutput('Clients created.')
            ->assertExitCode(0);
    }

    public function testYouCantCreateMultipleClients()
    {
        $this->artisan('passport:setup-initial-clients')
            ->expectsOutput('Clients created.')
            ->assertExitCode(0);

        $this->assertEquals(3, Passport::client()->count());

        $this->artisan('passport:setup-initial-clients')
            ->expectsOutput('The clients already exist!')
            ->assertExitCode(0);

        $this->assertEquals(3, Passport::client()->count());
    }

    public function testZapierClientExist(): void
    {
        tap(Passport::client()->forceFill([
            'id' => Client::ZAPIER_CLIENT_ID,
            'name' => Client::ZAPIER_CLIENT_NAME,
        ]))->save();

        $this->artisan('passport:setup-initial-clients')
            ->expectsOutput('Zapier client already exist!')
            ->assertExitCode(0);
    }
}
