<?php

namespace Ds\Domain\Commerce\Jobs\Webhooks;

use Ds\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AuthorizeNet extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /** @var array */
    protected $webhook;

    /**
     * Create a new job instance.
     *
     * @param array $webhook
     * @return void
     */
    public function __construct(array $webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug('Authorize.Net Webhook', ['data' => json_encode($this->webhook)]);
    }
}
