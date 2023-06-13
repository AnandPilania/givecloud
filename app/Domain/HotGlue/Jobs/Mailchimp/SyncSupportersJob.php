<?php

namespace Ds\Domain\HotGlue\Jobs\Mailchimp;

use Ds\Domain\HotGlue\HotGlue;
use Ds\Domain\HotGlue\Targets\MailchimpTarget;
use Ds\Domain\HotGlue\Transformers\Mailchimp\AccountTransformer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use League\Fractal\Resource\Collection as FractalCollection;

class SyncSupportersJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $supporters;

    public function __construct(Collection $supporters)
    {
        $this->supporters = $supporters;
    }

    public function handle()
    {
        $contact = new FractalCollection($this->supporters, new AccountTransformer, 'Customers');

        $state = app('fractal')->createArray($contact);

        app(HotGlue::class)
            ->client()
            ->post(app(MailchimpTarget::class)->url(), [
                'tap' => 'api',
                'state' => $state,
            ])->throw();
    }

    public function shouldQueue(): bool
    {
        return app(MailchimpTarget::class)->isEnabled()
            && app(MailchimpTarget::class)->isConnected()
            && app(MailchimpTarget::class)->isLinked();
    }

    public function viaQueue()
    {
        return 'low';
    }
}
