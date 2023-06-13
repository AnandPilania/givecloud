<?php

namespace Ds\Illuminate\View;

use Ds\Illuminate\View\Engines\PhpEngine;
use Ds\Illuminate\View\Engines\PhpEngineForIgnition;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerViewEngines();
        $this->registerViewExtensions();

        $this->registerBladeDirectives();
    }

    protected function registerViewEngines()
    {
        $this->app['view.engine.resolver']->register('php', function () {
            if (class_exists(\Facade\Ignition\Views\Engines\PhpEngine::class)) {
                return new PhpEngineForIgnition($this->app['files']);
            }

            return new PhpEngine($this->app['files']);
        });
    }

    protected function registerViewExtensions()
    {
        $this->app['view']->addExtension('html', 'php');
        $this->app['view']->addExtension('html.php', 'php');
    }

    protected function registerBladeDirectives()
    {
        Blade::directive('checked', function ($expression) {
            return "<?php if ($expression) echo 'checked'; ?>";
        });

        Blade::directive('disabled', function ($expression) {
            return "<?php if ($expression) echo 'disabled'; ?>";
        });

        Blade::directive('iftrue', function ($expression) {
            $code = preg_replace(
                '/^(.*?)(?:\s*,\s*((?<quote>\'|").*?\k{quote})|\s*,\s*(.*?))(?:\s*,\s*(.*)|)$/m',
                '<?php if ($1) { echo $2$4; } elseif ($5) { echo $5; } ?>',
                $expression
            );
            if ($code !== $expression) {
                return str_replace(['echo ;', 'elseif ()'], ["echo '';", 'elseif (false)'], $code);
            }

            return '';
        });

        Blade::directive('selected', function ($expression) {
            return "<?php if ($expression) echo 'selected'; ?>";
        });

        Blade::directive('set', function ($expression) {
            return preg_replace('/^.*?(?<quote>\'|")(.+)\k{quote}\s*,\s*(.*)$/', '<?php $$2 = $3; ?>', $expression);
        });

        Blade::directive('plural', function ($expression) {
            [$string, $count] = explode(', ', $expression);

            return "<?= e(\Illuminate\Support\Str::plural($string, $count)) ?>";
        });
    }
}
