<?php

namespace Ds\Domain\Zapier\Jobs;

use Ds\Models\ResthookSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class PostToZapier implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var \Illuminate\Http\Resources\Json\JsonResource */
    public $resourceToSend;

    /** @var int */
    public $resthookSubscriptionId;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    public function __construct(JsonResource $resourceToSend, int $resthookSubscriptionId)
    {
        $this->resourceToSend = $resourceToSend;
        $this->resthookSubscriptionId = $resthookSubscriptionId;
    }

    public function handle(): void
    {
        try {
            $resthookSubscription = ResthookSubscription::findOrFail($this->resthookSubscriptionId);

            if (false === $this->sendJobToResthookSubscription($resthookSubscription)) {
                // put the Job back into queue after 2s, 4s, 8s and 16s.
                $this->release(2 ** $this->attempts());
            }
        } catch (ModelNotFoundException $e) {
            report($e);
        }
    }

    protected function sendJobToResthookSubscription(ResthookSubscription $resthookSubscription): bool
    {
        try {
            $resthookResponse = Http::post($resthookSubscription->target_url, $this->resourceToSend->toArray(request()))->throw();

            return $resthookResponse->getStatusCode() === 200;
        } catch (RequestException $e) {
            // Subscription has been deleted on Zapier
            if ($e->getCode() === 410) {
                $resthookSubscription->delete();

                return true;
            }

            report($e);

            return false;
        } catch (HttpClientException $e) {
            report($e);

            return false;
        }
    }
}
