<?php

namespace Tests\Browser\Backend;

use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Backend\BackendDashboardPage;
use Tests\Browser\Pages\Backend\BackendLoginPage;
use Tests\DuskTestCase;

class BackendDashboardPageTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testDashboardShowsComponents()
    {
        $this->browse(function (Browser $browser) {
            $password = 'password-test';
            $user = $this->createUserWithPermissions(
                ['dashboard.'],
                ['hashed_password' => Hash::make($password)]
            );

            $date = fromLocal('now');
            $hour = $date->format('G');
            $month = $date->format('F');

            $browser
                ->visit(new BackendLoginPage)
                ->signIn($user->email, $password)
                ->visit(new BackendDashboardPage)
                ->assertSee('Dashboard')
                ->assertSee($hour >= 17 ? 'Evening' : ($hour >= 12 ? 'Afternoon' : 'Morning'))
                ->assertSee('Revenue in ' . $month)
                ->assertSee('Incomplete Contributions')
                ->assertSee('Cloud Storage Used')
                ->assertSee('User Accounts')
                ->assertSee('Contributions: Last 60 Days')
                ->assertSee('Today\'s Engagement')
                ->assertSee('Best Performing: 12 months')
                ->assertSee('Supporter Growth 30 Day')
                ->assertSee('Contributions by Geography Last 12 Months');
        });
    }
}
