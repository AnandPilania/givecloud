<?php

namespace Tests;

use Ds\Domain\Shared\Exceptions\MessageException;
use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (class_uses_recursive($this) as $trait) {
            $setUp = 'setUp' . class_basename($trait);

            if (method_exists($this, $setUp)) {
                $this->{$setUp}();
            }
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (class_uses_recursive($this) as $trait) {
            $tearDown = 'tearDown' . class_basename($trait);

            if (method_exists($this, $tearDown)) {
                $this->{$tearDown}();
            }
        }
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        // Add this here as Feature tests request do not go through public/index.php
        // and we are relying on this variable to be present elsewhere in the codebase.
        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        $app = require __DIR__ . '/../bootstrap/app.php';

        if (defined('APP_LEVEL_ENABLED')) {
            throw new MessageException('Running App-Level tests is not supported');
        }

        sys_get()->setupForTesting();

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
