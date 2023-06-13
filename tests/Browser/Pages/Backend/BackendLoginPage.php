<?php

namespace Tests\Browser\Pages\Backend;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Page;

class BackendLoginPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return route('login', [], false);
    }

    /**
     * Page assertions.
     */
    public function assert(Browser $browser): void
    {
        $browser
            ->assertPathIs($this->url())
            ->assertSee('Log In')
            ->assertSeeLink('Forgot your password?');
    }

    /**
     * Fill and submit the login form.
     */
    public function signIn(Browser $browser, ?string $email = null, ?string $password = null, ?bool $rememberMe = null): void
    {
        if ($rememberMe) {
            $browser->check('#inputRemember', $rememberMe);
        }

        $browser
            ->type('email', $email)
            ->type('password', $password)
            ->press('Continue');
    }
}
