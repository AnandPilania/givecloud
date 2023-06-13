<?php

namespace Ds\Providers;

use DDTrace\SpanData;
use DDTrace\Type;
use Illuminate\Support\ServiceProvider;

class DDTraceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        if (function_exists('DDTrace\trace_method')) {
            $this->setupMethodTraces();
        }
    }

    private function setupMethodTraces()
    {
        \DDTrace\trace_method(
            \Ds\Domain\Theming\Liquid\Template::class,
            'parse',
            function (SpanData $span) {
                $span->name = 'liquid.template.parse';
                $span->type = Type::WEB_SERVLET;
                $span->service = 'givecloud';
                $span->resource = $this->name;
            }
        );

        \DDTrace\trace_method(
            \Ds\Domain\Theming\Liquid\Template::class,
            'render',
            function (SpanData $span) {
                $span->name = 'liquid.template.render';
                $span->type = Type::WEB_SERVLET;
                $span->service = 'givecloud';
                $span->resource = $this->name;
            }
        );
    }
}
