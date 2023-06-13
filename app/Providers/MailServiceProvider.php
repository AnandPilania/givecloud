<?php

namespace Ds\Providers;

use Illuminate\Support\ServiceProvider;
use Swift_Plugins_LoggerPlugin;
use Swift_Plugins_Loggers_ArrayLogger;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->extend('mail.manager', function ($mailer, $app) {
            $mailer->getSwiftMailer()->registerPlugin($app['swift.plugins.logger']);

            return $mailer;
        });

        $this->app->singleton('swift.plugins.logger', function () {
            $logger = new Swift_Plugins_Loggers_ArrayLogger;

            return new Swift_Plugins_LoggerPlugin($logger);
        });
    }
}
