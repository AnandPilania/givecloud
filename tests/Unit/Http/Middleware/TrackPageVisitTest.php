<?php

namespace Tests\Unit\Http\Middleware;

use Ds\Http\Middleware\TrackPageVisit;
use Ds\Models\User;
use Ds\Models\UserPageVisit;
use Illuminate\Http\Request;
use Tests\TestCase;

class TrackPageVisitTest extends TestCase
{
    public function testCanTrackPageVisit(): void
    {
        $user = User::factory()->create();

        $this->actingAsUser($user);

        $this->checkTrackPageVisitMiddleware('backend.session.index');

        $this->assertDatabaseHas(UserPageVisit::class, [
            'user_id' => $user->id,
            'url' => route('backend.session.index', [], false),
        ]);

        $this->assertEquals(UserPageVisit::first()->user->id, $user->id);
    }

    public function testWillOnlyTrackGetRequests(): void
    {
        $user = User::factory()->create();

        $this->actingAsUser($user);

        $this->checkTrackPageVisitMiddleware('backend.pin_menu_items.store', null, Request::METHOD_POST);

        $this->assertDatabaseMissing(UserPageVisit::class, [
            'user_id' => $user->id,
            'url' => route('backend.pin_menu_items.store', [], false),
        ]);
    }

    public function testWillNotTrackFrontEndPages(): void
    {
        $user = User::factory()->create();
        $this->actingAsUser($user);

        $this->checkTrackPageVisitMiddleware('frontend.home');

        $this->assertDatabaseMissing(UserPageVisit::class, [
            'user_id' => $user->id,
            'url' => route('frontend.home', [], false),
        ]);
    }

    private function checkTrackPageVisitMiddleware(string $routeName, array $parameters = null, string $method = Request::METHOD_GET): void
    {
        $req = Request::create(route($routeName, $parameters, false), $method);

        $this->app->make(TrackPageVisit::class)->handle($req, function () {});
    }
}
