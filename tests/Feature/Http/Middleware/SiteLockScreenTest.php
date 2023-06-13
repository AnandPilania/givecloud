<?php

namespace Tests\Feature\Http\Middleware;

use Illuminate\Support\Str;
use Tests\TestCase;

class SiteLockScreenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        sys_set([
            'site_password' => Str::random(12),
            'site_password_message' => 'Site Locked',
        ]);
    }

    public function testExempt()
    {
        $this->get('/jpanel')->assertDontSee('Site Locked');
    }

    public function testNotExempt()
    {
        $this->get('/')->assertSee('Site Locked');
    }

    public function testUnlocked()
    {
        sys_set(['site_password' => null]);

        $this->get('/')->assertDontSee('Site Locked');
    }
}
