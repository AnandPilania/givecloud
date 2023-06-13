<?php

namespace Tests\Feature\Domain\Zapier;

use Tests\TestCase;

abstract class AbstractZapier extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        sys_set('zapier_enabled', true);
    }

    public function tearDown(): void
    {
        sys_set('zapier_enabled', false);

        parent::tearDown();
    }
}
