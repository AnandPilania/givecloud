<?php

namespace Tests\Unit\Domain\Zapier\Jobs;

use Ds\Domain\Zapier\Jobs\PostToZapier;
use Ds\Domain\Zapier\Resources\AccountResource;
use Ds\Exceptions\Handler;
use Ds\Models\Member;
use Ds\Models\ResthookSubscription;
use Ds\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group zapier
 */
class PostToZapierTest extends AbstractTriggers
{
    public function testHandleSuccess(): void
    {
        $user = $this->createUserWithAccountAndSubs('order.complete');

        Http::fake(['*' => Http::response()]);

        // Check that the Job is NOT back into the queue (release) when this error happens.
        $this->checkJobIsReleaseOrNot($user, 0)->handle();

        Http::assertSent(function (HttpClientRequest $request) {
            return $request->method() === HttpRequest::METHOD_POST;
        });
    }

    public function testHandleCannotFindResthookSubscription(): void
    {
        // Mock ExceptionhHandler used for reporting
        // when a ModelNotFoundException is thrown and catched.
        $exceptionHandlerMock = $this->createMock(Handler::class, ['report']);
        $exceptionHandlerMock
            ->expects($this->once())
            ->method('report')
            ->with((new ModelNotFoundException)->setModel(ResthookSubscription::class, [0]));
        $this->instance(ExceptionHandler::class, $exceptionHandlerMock);

        (new PostToZapier(new AccountResource(Member::factory()->create()), 0))->handle();
    }

    public function testHandleHookDeletedOnZapier(): void
    {
        $user = $this->createUserWithAccountAndSubs('order.complete');

        $this->assertPostToZapierHttpSent(Response::HTTP_GONE, new PostToZapier(
            new AccountResource($user->members->first()),
            $user->resthookSubscriptions->first()->getKey()
        ));
    }

    public function testHandleZapierUnhandledClientError(): void
    {
        $user = $this->createUserWithAccountAndSubs('order.complete');

        // Check that the Job is back into the queue (release) when this error happens.
        $this->assertPostToZapierHttpSent(Response::HTTP_BAD_REQUEST, $this->checkJobIsReleaseOrNot($user));
    }

    public function testHandleZapierUnhandledServerError(): void
    {
        $user = $this->createUserWithAccountAndSubs('order.complete');

        // Check that the Job is back into the queue (release) when this error happens.
        $this->assertPostToZapierHttpSent(Response::HTTP_INTERNAL_SERVER_ERROR, $this->checkJobIsReleaseOrNot($user));
    }

    protected function assertPostToZapierHttpSent(string $httpCode, PostToZapier $postToZapier): void
    {
        Http::fake(['*' => Http::response(null, $httpCode)]);

        $postToZapier->handle();

        Http::assertSent(function (HttpClientRequest $request) {
            return $request->method() === HttpRequest::METHOD_POST;
        });
    }

    public function checkJobIsReleaseOrNot(User $user, int $timesReleased = 1): PostToZapier
    {
        $postToZapierMock = $this
            ->getMockBuilder(PostToZapier::class)
            ->setConstructorArgs([
                'resourceToSend' => new AccountResource($user->members->first()),
                'resthookSubscriptionId' => $user->resthookSubscriptions->first()->getKey(),
            ])->onlyMethods(['release'])
            ->getMock();

        $postToZapierMock
            ->expects($this->exactly($timesReleased))
            ->method('release');

        return $postToZapierMock;
    }
}
