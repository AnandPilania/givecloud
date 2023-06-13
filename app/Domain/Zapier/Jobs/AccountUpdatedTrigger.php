<?php

namespace Ds\Domain\Zapier\Jobs;

use Ds\Domain\Zapier\Enums\Events;
use Ds\Domain\Zapier\Resources\AccountResource;
use Ds\Models\Member;
use Ds\Repositories\ResthookSubscriptionRepository;

class AccountUpdatedTrigger extends ZapierAbstractTrigger
{
    /** @var \Ds\Models\Member */
    protected $account;

    public function __construct(Member $account)
    {
        $this->account = $account;
    }

    public function handle(ResthookSubscriptionRepository $resthookSubscriptionRepository): void
    {
        $this->pushToZapier(Events::SUPPORTER_UPDATED, new AccountResource($this->account), $resthookSubscriptionRepository);
    }
}
