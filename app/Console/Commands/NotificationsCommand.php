<?php

namespace Ds\Console\Commands;

use Carbon\Carbon;
use Ds\Enums\RecurringPaymentProfileStatus;
use Illuminate\Console\Command;

class NotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications
                           {date? : Emulate running on this date.}
                           {--live : Send emails.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send time-based notifications to donors/customers.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($date = $this->argument('date')) {
            Carbon::setTestNow(fromLocal($date));
        }

        $this->processExpiringPaymentProfiles();
        $this->processExpiredPaymentProfiles();
        $this->processRecurringPaymentProfilesReminder();
        $this->processManualRecurringPaymentProfilesReminder();
        $this->processExpiredMemberships();
        $this->processSponsorshipBirthdays();
        $this->processSponsorshipAnniversaries();

        if ($date) {
            Carbon::setTestNow();
        }
    }

    /**
     * Process all soon to expire payment profiles.
     */
    public function processExpiringPaymentProfiles()
    {
        $paymentMethods = \Ds\Models\PaymentMethod::query()
            ->whereHas('recurringPaymentProfiles', fn ($query) => $query->where('status', RecurringPaymentProfileStatus::ACTIVE))
            ->expiring()
            ->get();

        if (count($paymentMethods)) {
            if (! $this->option('live')) {
                $this->comment(count($paymentMethods) . ' expiring cards.');
            }

            foreach ($paymentMethods as $paymentMethod) {
                if ($this->option('live')) {
                    member_notify_payment_method($paymentMethod, 'expiring');
                } else {
                    $this->comment(sprintf(
                        '| %3d  %32s  %18s  %19s  %15s',
                        $paymentMethod->id,
                        $paymentMethod->member->display_name,
                        $paymentMethod->account_number,
                        $paymentMethod->recurringPaymentProfiles()->count(),
                        $paymentMethod->use_as_default ? 'Y' : ''
                    ));
                }
            }
        } else {
            if (! $this->option('live')) {
                $this->comment('No expiring cards.');
            }
        }
    }

    /**
     * Process all recently expired payment profiles.
     */
    public function processExpiredPaymentProfiles()
    {
        $paymentMethods = \Ds\Models\PaymentMethod::query()
            ->whereHas('recurringPaymentProfiles', fn ($query) => $query->where('status', RecurringPaymentProfileStatus::ACTIVE))
            ->expired()
            ->get();

        if (count($paymentMethods)) {
            if (! $this->option('live')) {
                $this->comment(count($paymentMethods) . ' expired cards.');
            }

            foreach ($paymentMethods as $paymentMethod) {
                if ($this->option('live')) {
                    member_notify_payment_method($paymentMethod, 'expired');
                } else {
                    $this->comment(sprintf(
                        '| %3d  %32s  %18s  %19s  %15s',
                        $paymentMethod->id,
                        $paymentMethod->member->display_name,
                        $paymentMethod->account_number,
                        $paymentMethod->recurringPaymentProfiles()->count(),
                        $paymentMethod->use_as_default ? 'Y' : ''
                    ));
                }
            }
        } else {
            if (! $this->option('live')) {
                $this->comment('No expired cards.');
            }
        }
    }

    /**
     * Process all payment profiles that will be billed in 3 days.
     */
    public function processRecurringPaymentProfilesReminder()
    {
        $recurringPaymentProfiles = \Ds\Models\RecurringPaymentProfile::active()
            ->where('payment_mutex', false)
            ->whereRaw('datediff(?, next_billing_date) = ?', [toUtcFormat('today', 'date'), -3])
            ->get();

        if (count($recurringPaymentProfiles)) {
            if (! $this->option('live')) {
                $this->comment(count($recurringPaymentProfiles) . ' upcoming RPPs (3 days).');
            }

            foreach ($recurringPaymentProfiles as $rpp) {
                if ($this->option('live')) {
                    $rpp->notify('customer_recurring_payment_reminder');
                } else {
                    $this->comment(sprintf(
                        '| %3d  %32s  %18s  %17s  $%5d',
                        $rpp->id,
                        $rpp->member->display_name,
                        $rpp->member->email,
                        fromUtcFormat($rpp->next_billing_date, 'M d, Y'),
                        $rpp->amt
                    ));
                }
            }
        } else {
            if (! $this->option('live')) {
                $this->comment('No upcoming RPPs (3 days).');
            }
        }
    }

    /**
     * Process all manual recurring payment profile next bill dates.
     */
    public function processManualRecurringPaymentProfilesReminder()
    {
        $emails = \Ds\Models\Email::where('is_deleted', 0)->activeType('customer_manual_recurring_payment_reminder');

        if (count($emails)) {
            foreach ($emails as $email) {
                $recurringPaymentProfiles = \Ds\Models\RecurringPaymentProfile::query()
                    ->whereRaw('DATEDIFF(?, next_billing_date) = ?', [toUtcFormat('today', 'date'), $email->day_offset ?? 0])
                    ->active()
                    ->manual()
                    ->get();

                if (count($recurringPaymentProfiles)) {
                    if (! $this->option('live')) {
                        $this->comment(count($recurringPaymentProfiles) . ' manual RPPs (' . ($email->day_offset ?? 0) . ' days).');
                    }

                    foreach ($recurringPaymentProfiles as $rpp) {
                        if ($this->option('live')) {
                            $rpp->notify($email);
                        } else {
                            $this->comment(sprintf(
                                '| %3d  %32s  %18s  %17s  $%5d',
                                $rpp->id,
                                $rpp->member->display_name,
                                $rpp->member->email,
                                fromUtcFormat($rpp->next_billing_date, 'M d, Y'),
                                $rpp->amt
                            ));
                        }
                    }
                }
            }
        } else {
            if (! $this->option('live')) {
                $this->comment('!! No manual recurring payment reminders configured.');
            }
        }
    }

    /**
     * Process all memberships expired 10 days ago.
     */
    public function processExpiredMemberships()
    {
        $emails = \Ds\Models\Email::where('is_deleted', 0)->activeType('membership_expired');

        if (count($emails)) {
            foreach ($emails as $email) {
                // when detecting the datediff, we want to add one day
                // because the day AFTER the expiry date is the first date it expires
                $groupAccounts = \Ds\Models\GroupAccountTimespan::with('account', 'group')
                    ->whereIn('group_id', $email->memberships->pluck('id'))
                    ->whereRaw('DATEDIFF(?, group_account_timespan.end_date)-1 = ?', [fromLocal('today'), $email->day_offset ?? 0])->get();

                if (! $this->option('live')) {
                    $this->comment(count($groupAccounts) . ' expired/expiring membership (' . ($email->day_offset ?? 0) . ' days).');
                }

                // loop over each member who is expiring
                foreach ($groupAccounts as $groupAccount) {
                    if ($this->option('live')) {
                        $groupAccount->account->notify($email, [
                            'membership_expiry_date' => ($groupAccount->end_date) ? fromUtcFormat($groupAccount->end_date, 'F d, Y') : null,
                            'membership_name' => $groupAccount->group->name ?? null,
                            'membership_description' => $groupAccount->group->description ?? null,
                            'membership_renewal_url' => $groupAccount->group->rewewal_url ?? null,
                        ]);
                    } else {
                        $this->comment(sprintf(
                            '| %6d  %32s  %18s  %17s  %32s',
                            $groupAccount->account->id,
                            $groupAccount->account->display_name,
                            $groupAccount->account->email,
                            fromUtcFormat($groupAccount->end_date, 'M d, Y'),
                            $groupAccount->group->name
                        ));
                    }
                }
            }
        } else {
            if (! $this->option('live')) {
                $this->comment('!! No membership reminders configured.');
            }
        }
    }

    /**
     * Process all sponsor birthdays
     */
    public function processSponsorshipBirthdays()
    {
        $emails = \Ds\Models\Email::activeType('sponsorship_birthday');

        if (count($emails)) {
            foreach ($emails as $email) {
                // because birthdays are recurring every year,
                // we have to do 3 comparisons, one for each type of email
                $today = toUtcFormat('today', 'date');

                // if its BEFORE the birthday, look for NEXT BIRTHDAY
                if ($email->day_offset < 0) {
                    $sponsorships = \Ds\Domain\Sponsorship\Models\Sponsorship::whereRaw(
                        'DATEDIFF(?, DATE_ADD(birth_date, INTERVAL YEAR(?)-YEAR(birth_date) + IF(DAYOFYEAR(?) > DAYOFYEAR(birth_date),1,0) YEAR)) = ?',
                        [$today, $today, $today, $email->day_offset]
                    );

                // if its AFTER the birthday, look for LAST birthday
                } elseif ($email->day_offset > 0) {
                    $sponsorships = \Ds\Domain\Sponsorship\Models\Sponsorship::whereRaw(
                        'DATEDIFF(?, DATE_ADD(birth_date, INTERVAL YEAR(?)-YEAR(birth_date) + IF(DAYOFYEAR(?) > DAYOFYEAR(birth_date),1,0)-1 YEAR)) = ?',
                        [$today, $today, $today, $email->day_offset]
                    );

                // brithday on THE DAY OF
                } elseif ($email->day_offset === 0) {
                    $sponsorships = \Ds\Domain\Sponsorship\Models\Sponsorship::whereRaw("DATE_FORMAT(?, '%m-%d') = DATE_FORMAT(birth_date, '%m-%d')", [$today]);

                // otherwise bail
                } else {
                    return;
                }

                $sponsorships = $sponsorships->has('activeSponsors')->with('activeSponsors.member')->get();

                if (! $this->option('live')) {
                    $this->comment(count($sponsorships) . ' sponsorships with birthdays (' . ($email->day_offset ?? 0) . ' days).');
                }

                // send each email
                foreach ($sponsorships as $sponsorship) {
                    foreach ($sponsorship->activeSponsors as $sponsor) {
                        if ($this->option('live')) {
                            $sponsor->notify($email);
                        } else {
                            $this->comment(sprintf(
                                '| %3d  %32s  %18s  %17s',
                                $sponsor->id,
                                $sponsor->member->display_name,
                                $sponsor->member->email,
                                fromUtcFormat($sponsorship->birth_date, 'M d, Y')
                            ));
                        }
                    }
                }
            }
        } else {
            if (! $this->option('live')) {
                $this->comment('!! No sponsorship birthday reminders configured.');
            }
        }
    }

    /**
     * Process all sponsor anniversaries
     */
    public function processSponsorshipAnniversaries()
    {
        $emails = \Ds\Models\Email::activeType('sponsorship_anniversary');

        if (count($emails)) {
            foreach ($emails as $email) {
                // because anniversaries are recurring every year,
                // we have to do 3 comparisons, one for each type of email
                $today = toUtcFormat('today', 'date');

                // if its BEFORE the anniversary, look for NEXT anniversary
                if ($email->day_offset < 0) {
                    $sponsors = \Ds\Domain\Sponsorship\Models\Sponsor::whereRaw(
                        'DATEDIFF(?, DATE_ADD(started_at, INTERVAL YEAR(?)-YEAR(started_at) + IF(DAYOFYEAR(?) > DAYOFYEAR(started_at),1,0) YEAR)) = ?',
                        [$today, $today, $today, $email->day_offset]
                    );

                // if its AFTER the anniversary, look for LAST anniversary
                } elseif ($email->day_offset > 0) {
                    $sponsors = \Ds\Domain\Sponsorship\Models\Sponsor::whereRaw(
                        'DATEDIFF(?, DATE_ADD(started_at, INTERVAL YEAR(?)-YEAR(started_at) + IF(DAYOFYEAR(?) > DAYOFYEAR(started_at),1,0)-1 YEAR)) = ?',
                        [$today, $today, $today, $email->day_offset]
                    );

                // anniversary on THE DAY OF
                } elseif ($email->day_offset === 0) {
                    $sponsors = \Ds\Domain\Sponsorship\Models\Sponsor::whereRaw("DATE_FORMAT(?, '%m-%d') = DATE_FORMAT(started_at, '%m-%d')", [$today]);

                // otherwise bail
                } else {
                    return;
                }

                $sponsors->whereDate('started_at', '<>', $today);

                if (! $this->option('live')) {
                    $this->comment(sprintf(
                        '%s sponsorship anniversaries (%d days).',
                        $sponsors->count(),
                        $email->day_offset ?? 0
                    ));
                }

                // send each email
                foreach ($sponsors->with('sponsorship', 'member')->active()->get() as $sponsor) {
                    if ($this->option('live')) {
                        $sponsor->notify($email);
                    } else {
                        $this->comment(sprintf(
                            '| %3d  %32s  %18s  %17s',
                            $sponsor->id,
                            $sponsor->member->display_name,
                            $sponsor->member->email,
                            fromUtcFormat($sponsor->started_at, 'M d, Y')
                        ));
                    }
                }
            }
        } else {
            if (! $this->option('live')) {
                $this->comment('!! No sponsorship anniversary reminders configured.');
            }
        }
    }
}
