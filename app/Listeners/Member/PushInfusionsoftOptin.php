<?php

namespace Ds\Listeners\Member;

use Ds\Events\MemberOptinChanged;
use Ds\Services\InfusionsoftService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PushInfusionsoftOptin implements ShouldQueue
{
    use Queueable;

    /** @var \Ds\Services\InfusionsoftService */
    private $infusionsoftService;

    public function __construct(InfusionsoftService $infusionsoftService)
    {
        $this->infusionsoftService = $infusionsoftService;
    }

    public function handle(MemberOptinChanged $event): void
    {
        $memberOptingIn = $event->member->optinLogs->last()->action === 'optin';

        $pushed = $this->updateInfusionSoftTag($event->member->infusionsoft_contact_id, $memberOptingIn);

        if (! $pushed) {
            report(new Exception(sprintf(
                'An error happened when trying to push an %s for %s (#%d).',
                $memberOptingIn ? 'optin' : 'optout',
                $event->member->full_name,
                $event->member->getKey()
            )));
        }
    }

    public function shouldQueue(MemberOptinChanged $event): bool
    {
        if ($event->member->optinLogs->isEmpty()) {
            report(new Exception(sprintf(
                'Cannot update Member #%d (%s) without optin logs.',
                $event->member->getKey(),
                $event->member->full_name
            )));

            return false;
        }

        //  infusionsoft has to be installed.
        return sys_get('infusionsoft_token')
            && sys_get('infusionsoft_optin_tag');
    }

    public function viaQueue()
    {
        return 'low';
    }

    private function updateInfusionSoftTag(int $contactId, bool $isOptingIn): bool
    {
        $optinTags = [sys_get('infusionsoft_optin_tag')];

        if ($isOptingIn) {
            return $this->infusionsoftService->addUniqueContactTags($contactId, $optinTags);
        }

        try {
            $this->infusionsoftService->getClient()->removeTags($contactId, $optinTags);
        } catch (ModelNotFoundException $e) {
            return false;
        }

        return true;
    }
}
