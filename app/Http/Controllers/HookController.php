<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Webhook\Services\HookService;
use Ds\Http\Requests\HookStoreFormRequest;
use Ds\Http\Requests\HookUpdateFormRequest;
use Ds\Models\Hook;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Throwable;

class HookController extends Controller
{
    /** @var \Ds\Domain\Webhook\Services\HookService */
    private $hookService;

    public function __construct(HookService $hookService)
    {
        $this->hookService = $hookService;
    }

    public function index(): View
    {
        user()->canOrRedirect('hooks');

        pageSetup('Webhooks', 'jpanel');

        return view('hooks.index', ['hooks' => Hook::all()]);
    }

    public function create(): View
    {
        user()->canOrRedirect('hooks');

        pageSetup('Add webhook', 'jpanel');

        return view('hooks.create', ['hook' => new Hook]);
    }

    public function edit(Hook $hook): View
    {
        user()->canOrRedirect('hooks');

        pageSetup('Webhook', 'jpanel');

        $deliveries = $hook->deliveries()
            ->select(['id', 'guid', 'res_status', 'delivered_at'])
            ->orderBy('delivered_at', 'desc')
            ->take(100)
            ->get();

        return view('hooks.edit', compact('hook', 'deliveries'));
    }

    public function store(HookStoreFormRequest $request): Response
    {
        user()->canOrRedirect('hooks');

        return response([
            'success' => true,
            'hook_id' => $this->hookService->storeWithEvents(
                (bool) $request->active,
                $request->content_type,
                $request->payload_url,
                $request->secret,
                collect($request->events),
            )->getKey(),
        ]);
    }

    public function update(Hook $hook, HookUpdateFormRequest $request): Response
    {
        user()->canOrRedirect('hooks');

        if ($request->has('insecure_ssl')) {
            return response([
                'success' => true,
                'hook_id' => $this->hookService
                    ->updateInsecureSSL($hook, (bool) $request->insecure_ssl)
                    ->getKey(),
            ]);
        }

        return response([
            'success' => true,
            'hook_id' => $this->hookService->updateWithEvents(
                $hook,
                $request->has('active') ? true : false,
                $request->content_type,
                $request->payload_url,
                $request->secret,
                collect($request->events),
            )->getKey(),
        ]);
    }

    public function destroy(Hook $hook): Response
    {
        user()->canOrRedirect('hooks');

        try {
            $hook->delete();

            return response(['success' => true]);
        } catch (Throwable $e) {
            return response(['message' => 'Unable to delete webhook'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
