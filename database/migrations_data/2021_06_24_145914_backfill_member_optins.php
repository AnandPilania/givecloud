<?php

use Carbon\Carbon;
use Ds\Enums\MemberOptinAction;
use Ds\Enums\MemberOptinSource;
use Ds\Models\Member;
use Ds\Models\MemberOptinLog;
use Illuminate\Database\Migrations\Migration;

class BackfillMemberOptins extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (Member::with('orders', 'ownLoginLogs')->lazy() as $member) {
            if ($member->orders->isEmpty()) {
                /** @var \Ds\Models\MemberLogin */
                $firstLogin = $member->ownLoginLogs->last();

                if (empty($member->created_at)) {
                    continue;
                }

                $member->optinLogs()->save(
                    $this->makeBackfillOptin(
                        $member->email_opt_in_deprecated,
                        $member->created_at,
                        $firstLogin->ip ?? null,
                        $firstLogin ? $firstLogin->ua()->toString() : null
                    )
                );

                continue;
            }

            if (empty($member->firstOrder->created_at)) {
                continue;
            }

            $member->optinLogs()->save(
                $this->makeBackfillOptin(
                    $member->firstOrder->email_opt_in,
                    $member->firstOrder->created_at,
                    $member->firstOrder->client_ip,
                    $member->firstOrder->ua()->toString()
                )
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MemberOptinLog::where('source', MemberOptinSource::BACKFILL)->delete();
    }

    private function makeBackfillOptin(
        bool $isOptin,
        Carbon $createdAt,
        ?string $ip = null,
        ?string $userAgent = null
    ): MemberOptinLog {
        return (new MemberOptinLog)->fill([
            'action' => $isOptin ? MemberOptinAction::OPTIN : MemberOptinAction::OPTOUT,
            'created_at' => $createdAt,
            'ip' => $ip,
            'reason' => $isOptin ? null : 'Opted out by backfilling.',
            'source' => MemberOptinSource::BACKFILL,
            'user_agent' => $userAgent,
        ]);
    }
}
