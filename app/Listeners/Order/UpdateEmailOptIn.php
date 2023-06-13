<?php

namespace Ds\Listeners\Order;

use Ds\Enums\MemberOptinSource;
use Ds\Events\OrderWasCompleted;
use Ds\Services\MemberService;

class UpdateEmailOptIn
{
    /** @var \Ds\Services\MemberService */
    private $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * Handle the event.
     *
     * @param \Ds\Events\OrderWasCompleted $event
     * @return void
     */
    public function handle(OrderWasCompleted $event)
    {
        if (! $event->order->email_opt_in || ! $event->order->member || $event->order->member->email_opt_in) {
            return;
        }

        $this->memberService
            ->setMember($event->order->member)
            ->optin(MemberOptinSource::CHECKOUT);
    }
}
