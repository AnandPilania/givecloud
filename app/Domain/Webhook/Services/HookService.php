<?php

namespace Ds\Domain\Webhook\Services;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Webhook\Mail\WebhookFailed;
use Ds\Domain\Webhook\Repositories\HookRepository;
use Ds\Models\Hook;
use Ds\Models\HookDelivery;
use Ds\Models\HookEvent;
use Ds\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use League\Fractal\Resource\ResourceInterface;
use Throwable;

class HookService
{
    /** @var \Ds\Domain\Webhook\Repositories\HookRepository */
    protected $hookRepository;

    public function __construct(HookRepository $hookRepository)
    {
        $this->hookRepository = $hookRepository;
    }

    /**
     * @throws \Throwable
     */
    public function storeWithEvents(
        bool $active,
        string $contentType,
        string $payloadUrl,
        ?string $secret,
        Collection $events
    ): Hook {
        $hook = Hook::createOrFail([
            'active' => $active,
            'content_type' => $contentType,
            'insecure_ssl' => false,
            'payload_url' => $payloadUrl,
            'secret' => $secret ?: Str::random(40),
        ]);

        return $this->saveEventsByName($hook, $events);
    }

    /**
     * @throws \Throwable
     */
    public function updateInsecureSSL(Hook $hook, bool $insecureSSL): Hook
    {
        return $hook->updateOrFail(['insecure_ssl' => $insecureSSL]);
    }

    /**
     * @throws \Throwable
     */
    public function updateWithEvents(
        Hook $hook,
        bool $active,
        ?string $contentType,
        ?string $payloadUrl,
        ?string $secret,
        ?Collection $events = null
    ): Hook {
        $hook->updateOrFail([
            'active' => $active,
            'content_type' => $contentType,
            'payload_url' => $payloadUrl,
            'secret' => $secret,
        ]);

        if ($events) {
            if ($hook->events->isNotEmpty()) {
                $idsToDelete = $hook->events->map->getKey()->diff($events);
                if ($idsToDelete->isNotEmpty()) {
                    $hook->events()->whereIn('id', $idsToDelete)->delete();
                }
            }

            $this->saveEventsByName($hook, $events->filter(function ($event) {
                return (int) $event === 0; // strings !== '1' give '0'
            }));
        }

        return $hook;
    }

    /**
     * Should a delivery take place for a given event.
     */
    public function shouldDeliver(string $eventName): bool
    {
        return $this->hookRepository
            ->byActiveAndEvent($eventName)
            ->count() > 0;
    }

    /**
     * Make deliveries for active hooks.
     *
     * @param \League\Fractal\Resource\ResourceInterface|\Illuminate\Http\Resources\Json\JsonResource $resource
     */
    public function makeDeliveries(string $eventName, $resource): void
    {
        $payload = json_encode($this->getDeliveryPayloadArray($resource));

        $this->hookRepository
            ->getActiveByEvent($eventName)
            ->each(function ($hook) use ($payload) {
                $hook->events->each(function ($event) use ($hook, $payload) {
                    // TODO: extract to a job?
                    $delivery = new HookDelivery;
                    $delivery->hook_id = $hook->getKey();
                    $delivery->event = $event->name;
                    $delivery->guid = (string) Str::uuid();
                    $delivery->delivered_at = fromUtc('now');
                    $delivery->completed_in = 0;
                    $delivery->setRelation('hook', $hook);

                    $this->deliver($delivery, $payload);
                });
            });
    }

    public function redeliver(HookDelivery $delivery): void
    {
        $this->deliver($delivery, $delivery->req_body);
    }

    protected function deliver(HookDelivery $delivery, string $payload): void
    {
        $headers = [
            'User-Agent' => 'Givecloud-Hookshot/bcde9240',
            'X-Givecloud-Event' => $delivery->event,
            'X-Givecloud-Delivery' => $delivery->guid,
            'X-Givecloud-Domain' => site('subdomain'),
        ];

        if ($delivery->hook->secret) {
            $headers['X-Givecloud-Signature'] = hash_hmac('sha1', $payload, $delivery->hook->secret);
        }

        $req = Http::withOptions([
            'headers' => $headers,
            'verify' => $delivery->hook->insecure_ssl,
        ])->withBody($payload, $delivery->hook->content_type);

        $delivery->req_headers = collect([
            'Request Method: POST',
            "Request URL: {$delivery->hook->payload_url}",
            $req->getHeadersAsString(),
        ])->implode("\n");

        $delivery->req_body = $payload;

        try {
            $res = $req->post($delivery->hook->payload_url);
        } catch (Throwable $e) {
            $delivery->res_status = -1;
            $delivery->res_body = $e->getMessage();
            $delivery->save();
            $this->notifyFailure($delivery, $e->getMessage());

            return;
        }

        /** @var \Psr\Http\Message\RequestInterface */
        $req = $res->getRequest();

        $delivery->req_body = (string) $req->getBody();
        $delivery->req_headers = collect([
            sprintf('Request Method: %s', (string) $req->getMethod()),
            sprintf('Request URL: %s', (string) $req->getUri()),
            $res->getRequestHeadersAsString(),
        ])->implode("\n");

        $delivery->completed_in = (float) optional($res->getTransferStats())->getTransferTime();

        $delivery->res_status = $res->getStatusCode();
        $delivery->res_headers = $res->getHeadersAsString();
        $delivery->res_body = (string) $res->getBody();
        $delivery->save();

        if ($delivery->res_status >= 400) {
            $this->notifyFailure($delivery, 'HTTP Error ' . $delivery->res_status);
        }
    }

    /**
     * @throws \Exception
     */
    protected function getDeliveryPayloadArray($resource): array
    {
        if (is_a($resource, ResourceInterface::class)) {
            return app('fractal')->createArray($resource);
        }

        if (is_a($resource, JsonResource::class)) {
            return $resource->toArray(new Request());
        }

        throw new MessageException('Unsupported resource type ' . get_class($resource) . ' use as paylod in hook.');
    }

    protected function notifyFailure(HookDelivery $delivery, ?string $error = null): void
    {
        User::mailAccountAdmins(new WebhookFailed($delivery, $error));
    }

    private function saveEventsByName(Hook $hook, Collection $eventNames): Hook
    {
        if ($eventNames->isEmpty()) {
            return $hook;
        }

        $hook->events()->saveMany($eventNames->map(function ($eventName) {
            $newEvent = new HookEvent;
            $newEvent->name = $eventName;

            return $newEvent;
        }));

        return $hook;
    }
}
