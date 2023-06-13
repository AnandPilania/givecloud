<?php

namespace Ds\Domain\Zapier\Controllers;

use Ds\Domain\Zapier\Requests\ResthookSubscriptionStoreFormRequest;
use Ds\Domain\Zapier\Services\ResthookSubscriptionService;
use Ds\Models\ResthookSubscription;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResthookSubscriptionsController
{
    /** @var \Ds\Domain\Zapier\Services\ResthookSubscriptionService */
    protected $resthookSubscriptionService;

    public function __construct(ResthookSubscriptionService $resthookSubscriptionService)
    {
        $this->resthookSubscriptionService = $resthookSubscriptionService;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->resthookSubscriptionService->index());
    }

    public function show(ResthookSubscription $resthookSubscription): JsonResponse
    {
        return response()->json($resthookSubscription);
    }

    public function store(ResthookSubscriptionStoreFormRequest $request): JsonResponse
    {
        $resthookSubscription = $this->resthookSubscriptionService->store(
            $request->event,
            $request->target_url,
            (int) $request->user('passport')->getKey()
        );

        if ($resthookSubscription) {
            return response()->json($resthookSubscription, Response::HTTP_CREATED);
        }

        return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function update(
        ResthookSubscriptionStoreFormRequest $request,
        ResthookSubscription $resthookSubscription
    ): JsonResponse {
        $updateSuccessful = $this->resthookSubscriptionService->update(
            $resthookSubscription,
            $request->event,
            $request->target_url,
            (int) $request->user('passport')->getKey()
        );

        return response()->json(
            $resthookSubscription,
            $updateSuccessful ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    public function destroy(ResthookSubscription $resthookSubscription): JsonResponse
    {
        return response()->json(
            [],
            $resthookSubscription->delete() ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
