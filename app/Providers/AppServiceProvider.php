<?php

namespace Ds\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Support\DateFactory::use(\Ds\Domain\Shared\DateTime::class);

        \Carbon\Carbon::setToStringFormat('M j, Y');

        // default to using Bootstrap 3 pagination
        \Illuminate\Pagination\Paginator::useBootstrapThree();

        // register additional validation customization
        $this->app['validator']->resolver(function ($translator, $data, $rules, $messages) {
            return new \Ds\Illuminate\Validation\Validator($translator, $data, $rules, $messages);
        });

        $this->definePolymorphicMapNames();

        Passport::hashClientSecrets();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal() && class_exists(\Laravel\Telescope\TelescopeApplicationServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        // register the service for blink cache
        $this->app->singleton(\Ds\Domain\Shared\BlinkCache::class, function ($app) {
            return new \Ds\Domain\Shared\BlinkCache;
        });

        // register binding for member account
        $this->app->bind(\Ds\Models\Member::class, function () {
            return member() ?? new \Ds\Models\Member;
        });

        // register the flash service
        $this->app->singleton('flash', function () {
            $flash = new \Ds\Services\FlashService([
                'error' => 'alert alert-danger alert-dismiss',
                'success' => 'alert alert-success alert-dismiss',
                'notice' => 'alert alert-info alert-dismiss',
            ]);
            $flash->setAutoescape(false);

            return $flash;
        });

        // register the hashids service
        // provides ability to encrypt/decrypt ids
        $this->app->bind('hashids', function () {
            return new \Hashids\Hashids('', 8, 'ABCDEFGHJKLMNPRSTUVWXYZ23456789');
        });

        // register the service for recaptcha
        $this->app->singleton('recaptcha', function ($app) {
            if (sys_get('captcha_type') === 'hcaptcha') {
                return new \Ds\Common\HCaptchaClient(
                    $app->config['services.hcaptcha.site_key'],
                    $app->config['services.hcaptcha.secret_key'],
                    $app['session']
                );
            }

            return new \Ds\Common\ReCaptchaClient(
                $app->config['services.recaptcha.site_key'],
                $app->config['services.recaptcha.secret_key'],
                $app['session']
            );
        });

        // register the service for faker
        $this->app->singleton(\Faker\Generator::class, function ($app) {
            $faker = \Faker\Factory::create($app['config']->get('app.faker_locale', 'en_US'));
            $faker->addProvider(new \Bezhanov\Faker\Provider\Avatar($faker));
            $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));
            $faker->addProvider(new \Bezhanov\Faker\Provider\Device($faker));
            $faker->addProvider(new \Bezhanov\Faker\Provider\Placeholder($faker));
            $faker->addProvider(new \Ds\Common\Faker\RealAddressProvider($faker));
            $faker->addProvider(new \Ds\Common\Faker\PhoneNumbers\CanadaPhoneNumberProvider($faker));

            return $faker;
        });

        // register the service for geoip
        $this->app->singleton('geoip', function ($app) {
            return new \Ds\Services\GeoIpService(
                $app['request'],
                $app['config']->get('services.geoip.database')
            );
        });

        // register the service for ip2proxy
        $this->app->singleton('ip2proxy', function ($app) {
            return new \Ds\Common\Ip2Proxy(
                $app['request'],
                $app['config']->get('services.ip2proxy.database')
            );
        });

        // register the theme
        $this->app->singleton('theme', function ($app) {
            $theme = \Ds\Models\Theme::findOrFail(sys_get('active_theme'));

            return new \Ds\Domain\Theming\Theme($app['config'], $theme);
        });

        // register the scss compiler
        $this->app->singleton('scss', function ($app) {
            $theme = \Ds\Models\Theme::findOrFail(sys_get('active_theme'));

            return new \Ds\Domain\Theming\ScssCompiler($theme);
        });

        $this->app->singleton(\Ds\Domain\Commerce\Shipping\Carriers\CanadaPost::class, function () {
            return new \Ds\Domain\Commerce\Shipping\Carriers\CanadaPost(
                sys_get('shipping_canadapost_customer_number'),
                sys_get('shipping_canadapost_user'),
                sys_get('shipping_canadapost_pass')
            );
        });

        $this->app->singleton(\Ds\Domain\Commerce\Shipping\Carriers\FedEx::class, function () {
            return new \Ds\Domain\Commerce\Shipping\Carriers\FedEx(
                sys_get('shipping_fedex_key'),
                sys_get('shipping_fedex_pass'),
                sys_get('shipping_fedex_account'),
                sys_get('shipping_fedex_meter'),
                sys_get('shipping_fedex_net_discount'),
                sys_get('list:shipping_fedex_servicecodes')
            );
        });

        $this->app->singleton(\Ds\Domain\Commerce\Shipping\Carriers\UPS::class, function () {
            return new \Ds\Domain\Commerce\Shipping\Carriers\UPS(
                sys_get('shipping_ups_access_key'),
                sys_get('shipping_ups_user'),
                sys_get('shipping_ups_pass'),
                sys_get('list:shipping_ups_servicecodes'),
                sys_get('shipping_ups_account'),
                sys_get('bool:shipping_ups_negotiated_rates'),
                true
            );
        });

        $this->app->singleton(\Ds\Domain\Commerce\Shipping\Carriers\USPS::class, function () {
            return new \Ds\Domain\Commerce\Shipping\Carriers\USPS(
                sys_get('shipping_usps_user'),
                sys_get('shipping_usps_pass'),
                sys_get('list:shipping_usps_classids'),
                sys_get('list:shipping_usps_interids')
            );
        });

        // register the shortcode container
        $this->app->singleton('shortcodes', \Ds\Domain\Theming\ShortcodeContainer::class);

        // register useragent parser
        $this->app->singleton('ua', function () {
            return \UAParser\Parser::create();
        });

        $this->app->bind('swap.http_client', function () {
            return new \GuzzleHttp\Client(['connect_timeout' => 1, 'timeout' => 2]);
        });

        Passport::ignoreMigrations();

        if (sys_get()->inTestingEnvironment()) {
            $this->registerForTestingEnvironment();
        }
    }

    private function definePolymorphicMapNames(): void
    {
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'category' => \Ds\Models\Category::class,
            'account' => \Ds\Models\Member::class,
            'order' => \Ds\Models\Order::class,
            'payment' => \Ds\Models\Payment::class,
            'post' => \Ds\Models\Post::class,
            'post_type' => \Ds\Models\PostType::class,
            'product' => \Ds\Models\Product::class,
            'refund' => \Ds\Models\Refund::class,
            'tax_receipt' => \Ds\Models\TaxReceipt::class,
            'user' => \Ds\Models\User::class,
        ]);
    }

    private function registerForTestingEnvironment(): void
    {
        $this->app->singleton('swap', \Tests\Fakes\FakeSwap::class);
    }
}
