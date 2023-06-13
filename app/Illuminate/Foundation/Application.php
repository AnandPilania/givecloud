<?php

namespace Ds\Illuminate\Foundation;

use Illuminate\Foundation\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * The application namespace.
     *
     * @var string
     */
    protected $namespace = 'Ds\\';

    /**
     * Determine if application is in local environment.
     *
     * @return bool
     */
    public function isLocal()
    {
        return parent::isLocal() || isDev();
    }
}
