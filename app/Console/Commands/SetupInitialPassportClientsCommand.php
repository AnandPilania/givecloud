<?php

namespace Ds\Console\Commands;

use Ds\Domain\Zapier\Services\ZapierSettingsService;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class SetupInitialPassportClientsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:setup-initial-clients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uses passport to setup the initial clients';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(ClientRepository $clients)
    {
        if ($this->findPersonalAccessClient()) {
            $this->error('The clients already exist!');

            return;
        }

        $this->createPersonalAccessClient($clients);
        $this->createPasswordAccessClient($clients);

        $this->createZapierClient();

        $this->info('Clients created.');
    }

    private function findPersonalAccessClient(): ?Client
    {
        return Passport::client()
            ->where('personal_access_client', 1)
            ->first();
    }

    private function createPersonalAccessClient(ClientRepository $clients): void
    {
        $personalAccessClient = $clients->createPersonalAccessClient(
            null,
            'Givecloud Personal Access Client',
            'http://localhost'
        );

        $this->setPassportPersonalAccessClient($personalAccessClient);
    }

    private function createPasswordAccessClient(ClientRepository $clients): void
    {
        $clients->createPasswordGrantClient(
            null,
            'Givecloud Password Grant Client',
            'http://localhost',
            'users'
        );
    }

    private function createZapierClient(): void
    {
        if (app(ZapierSettingsService::class)->clientExists()) {
            $this->error('Zapier client already exist!');

            return;
        }

        $client = Passport::client();

        $client->forceFill([
            'id' => \Ds\Models\Passport\Client::ZAPIER_CLIENT_ID,
            'name' => \Ds\Models\Passport\Client::ZAPIER_CLIENT_NAME,
            'secret' => config('services.zapier.client_secret'),
            'redirect' => config('services.zapier.redirect'),
        ]);

        try {
            $client->save();
        } catch (QueryException $exception) {
            $this->error('An error occurred, Zapier needs to have its ID set to ' . \Ds\Models\Passport\Client::ZAPIER_CLIENT_ID);
        }
    }

    private function setPassportPersonalAccessClient(Client $personalAccessClient): void
    {
        sys_set('passport_personal_access_client_id', $personalAccessClient->id);
        sys_set('passport_personal_access_client_secret', $personalAccessClient->plainSecret);
    }
}
