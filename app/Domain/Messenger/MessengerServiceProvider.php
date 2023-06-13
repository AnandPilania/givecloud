<?php

namespace Ds\Domain\Messenger;

use BotMan\BotMan\Container\LaravelContainer;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Http\Curl;
use BotMan\BotMan\Storages\Drivers\FileStorage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class MessengerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // override Twilio configration with
        // the subaccount credentials
        Config::set([
            'botman.twilio.sid' => sys_get('twilio_subaccount_sid'),
            'botman.twilio.token' => sys_get('twilio_subaccount_token'),
        ]);

        $this->loadDrivers();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BotMan::class, function ($app) {
            $config = $app['config']->get('botman');

            $driverManager = new DriverManager($config, new Curl);

            // If we using the request from the container then BotMan
            // sometimes selects the NullDriver because the StringTrim middleware
            // invalidates the payload signature
            $driver = $driverManager->getMatchingDriver(
                \Symfony\Component\HttpFoundation\Request::createFromGlobals()
            );

            $botman = new BotMan(
                new Cache,
                $driver,
                $config,
                new FileStorage(storage_path('botman'))
            );

            $botman->setContainer(new LaravelContainer($app));

            return $botman;
        });

        $this->app->alias(BotMan::class, 'botman');
    }

    /**
     * Load all the supported drivers.
     */
    protected function loadDrivers()
    {
        $drivers = [
            \Ds\Domain\Messenger\TwilioMessageDriver::class,
            \Ds\Domain\Messenger\NexmoDriver::class,
            \BotMan\Drivers\Web\WebDriver::class,
        ];

        foreach ($drivers as $driver) {
            DriverManager::loadDriver($driver);
        }
    }
}
