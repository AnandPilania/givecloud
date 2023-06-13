<?php

namespace Tests\Unit\Domain\Zapier\Jobs;

use Ds\Domain\Zapier\Jobs\PostToZapier;
use Ds\Models\Member;
use Ds\Models\ResthookSubscription;
use Ds\Models\User;
use Ds\Repositories\ResthookSubscriptionRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

abstract class AbstractTriggers extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

    /** @var \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model */
    protected function mockAndcallTrigger(string $triggerClassName, Collection $resthookSubscriptions, $model): void
    {
        $this->callTrigger($triggerClassName, $model);

        Bus::assertDispatchedTimes(PostToZapier::class, $resthookSubscriptions->count());
        Bus::assertDispatched(PostToZapier::class, function (PostToZapier $jobs) use ($resthookSubscriptions) {
            $resthookSubscriptionIds = $resthookSubscriptions->pluck('id');

            return Collection::wrap($jobs)
                ->filter(function ($job) use ($resthookSubscriptionIds) {
                    return $resthookSubscriptionIds->contains($job->resthookSubscriptionId);
                });
        });
    }

    /**
     * @param string $triggerClassName
     * @param \Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection $resource
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function callTrigger(string $triggerClassName, $resource): void
    {
        (new $triggerClassName($resource))->handle($this->app->make(ResthookSubscriptionRepository::class));
    }

    protected function createUserWithAccountAndSubs(string $eventName, int $subscriptionsCount = 1): User
    {
        $user = User::factory()->api()->create();
        $account = Member::factory()->create();
        $user->members()->save($account);

        $resthookSubscriptions = Collection::wrap(
            ResthookSubscription::factory()
                ->count($subscriptionsCount > 1 ? $subscriptionsCount : null)
                ->create(['event' => $eventName])
        );
        $user->resthookSubscriptions()->saveMany($resthookSubscriptions);

        return $user;
    }
}
