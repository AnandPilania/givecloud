<?php

namespace Ds\Domain\Flatfile\Jobs;

use Ds\Enums\MemberOptinSource;
use Ds\Models\AccountType;
use Ds\Models\Member;
use Ds\Models\Membership;
use Ds\Services\MemberService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ImportSupportersJob extends ImportJob
{
    private static $memberships;
    private static $accountTypes;

    protected function importRow(array $row): void
    {
        if ($row['email'] && $member = Member::where('email', $row['email'])->first()) {
            $member->updated_by = data_get($this->batchMetaData, '__endUser__.userId');
        } else {
            $member = new Member;
            $member->created_at = fromUtcFormat('now', 'datetime');
            $member->created_by = data_get($this->batchMetaData, '__endUser__.userId');
            $member->updated_by = data_get($this->batchMetaData, '__endUser__.userId');
        }

        $member->updated_at = fromUtcFormat('now', 'datetime');

        // loop over all column headers in the order we are expecting them
        foreach ($this->schema() as $column => $definition) {
            // if there is no value in this cell, skip it
            if (! isset($row[$column])) {
                continue;
            }

            // get a cleaned up version of the cell
            $value = $row[$column];

            if ($column === 'membership_name') {
                $membership = $this->memberships()->get($value);
            } elseif ($column === 'account_type') {
                $accountType = $this->accountTypes()->get($value);
                $member->account_type_id = $accountType->id;
            } elseif ($column === 'password') {
                $member->password = bcrypt($value);
            } elseif ($column === 'created_at') {
                $member->created_at = toUtcFormat($value, 'datetime') ?? fromUtcFormat('now', 'datetime');
            } elseif ($column === 'membership_starts_on') {
                $membership_starts_on = toUtcFormat($value, 'date');
            } elseif ($column === 'membership_expires_on') {
                $membership_expires_on = toUtcFormat($value, 'date');
            } elseif ($column === 'email_opt_in') {
                $optIn = $value;
            } else {
                $member->{$column} = $value;
            }
        }

        if ($accountType->is_organization ?? false) {
            $member->display_name = $member->bill_organization_name ?? null;
        } else {
            $member->display_name = trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? ''));
        }

        $member->save();

        if (isset($optIn)) {
            app(MemberService::class)
                ->setMember($member)
                ->updateOptin(Str::boolify($optIn), null, MemberOptinSource::IMPORT);
        }

        if (isset($membership)) {
            $groupAccount = $member->addUniqueGroup($membership, $membership_starts_on ?? $member->created_at, 'Import');

            if (isset($membership_expires_on)) {
                $groupAccount->end_date = $membership_expires_on;
                $groupAccount->save();
            }
        }
    }

    protected function memberships(): Collection
    {
        if (static::$memberships) {
            return static::$memberships;
        }

        return static::$memberships = Membership::select(['id', 'name'])->pluck('id', 'name');
    }

    protected function accountTypes(): Collection
    {
        if (static::$accountTypes) {
            return static::$accountTypes;
        }

        return static::$accountTypes = AccountType::select(['id', 'is_organization', 'name'])->get()->keyBy('name');
    }
}
