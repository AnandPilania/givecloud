<?php

namespace Ds\Listeners\Supporter;

use Ds\Events\OrderWasCompleted;
use Ds\Models\Order;

class UpdateSupporterLocalization
{
    public function handle(OrderWasCompleted $event): void
    {
        if (! $this->shouldHandle($event->order)) {
            return;
        }

        $member = $event->order->member;

        if ($event->order->currency_code) {
            $member->currency_code ??= $event->order->currency_code;
        }

        if ($event->order->timezone) {
            $member->timezone ??= $event->order->timezone;
        }

        if ($event->order->language) {
            $member->language ??= $event->order->language;
        }

        if ($member->isDirty()) {
            $member->saveQuietly();
        }
    }

    public function shouldHandle(Order $order): bool
    {
        if ($order->is_pos) {
            return false;
        }

        if ($order->created_by === config('givecloud.super_user_id')) {
            return false;
        }

        if ($order->member === null) {
            return false;
        }

        return true;
    }
}
