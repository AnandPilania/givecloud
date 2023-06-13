<?php

namespace Ds\Providers;

use Illuminate\Support\ServiceProvider;

class MixinServiceProvider extends ServiceProvider
{
    /** @var array<string,string> */
    private $mixins = [
        \Illuminate\Cache\Repository::class => \Ds\Illuminate\Cache\CacheMixin::class,
        \Illuminate\Console\Command::class => \Ds\Illuminate\Console\CommandMixin::class,
        \Illuminate\Database\Eloquent\Builder::class => \Ds\Illuminate\Database\Eloquent\BuilderMixin::class,
        \Illuminate\Database\Query\Builder::class => \Ds\Illuminate\Database\BuilderMixin::class,
        \Illuminate\Database\Schema\Blueprint::class => \Ds\Illuminate\Database\BlueprintMixin::class,
        \Illuminate\Database\Schema\Grammars\Grammar::class => \Ds\Illuminate\Database\GrammarMixin::class,
        \Illuminate\Http\Client\Factory::class => \Ds\Illuminate\Http\Client\FactoryMixin::class,
        \Illuminate\Http\Client\PendingRequest::class => \Ds\Illuminate\Http\Client\PendingRequestMixin::class,
        \Illuminate\Http\Client\Response::class => \Ds\Illuminate\Http\Client\ResponseMixin::class,
        \Illuminate\Http\RedirectResponse::class => \Ds\Illuminate\Http\RedirectResponseMixin::class,
        \Illuminate\Http\Request::class => \Ds\Illuminate\Http\RequestMixin::class,
        \Illuminate\Routing\Redirector::class => \Ds\Illuminate\Routing\RedirectorMixin::class,
        \Illuminate\Routing\ResponseFactory::class => \Ds\Illuminate\Http\ResponseMixin::class,
        \Illuminate\Routing\Router::class => \Ds\Illuminate\Routing\RouterMixin::class,
        \Illuminate\Routing\UrlGenerator::class => \Ds\Illuminate\Routing\UrlGeneratorMixin::class,
        \Illuminate\Support\Arr::class => \Ds\Illuminate\Support\ArrMixin::class,
        \Illuminate\Support\Collection::class => \Ds\Illuminate\Support\CollectionMixin::class,
        \Illuminate\Support\Str::class => \Ds\Illuminate\Support\StrMixin::class,
        \Illuminate\Testing\TestResponse::class => \Ds\Illuminate\Testing\TestResponseMixin::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->mixins as $class => $mixin) {
            $class::mixin(new $mixin);
        }
    }
}
