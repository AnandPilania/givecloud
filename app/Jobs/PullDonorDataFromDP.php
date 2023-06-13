<?php

namespace Ds\Jobs;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\AccountType;
use Ds\Models\Member;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullDonorDataFromDP extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * Create a new job instance.
     *
     * @param array $options
     * @return void
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // only loop over donors with ids
        $all_members = Member::whereNotNull('donor_id');

        // account types ['IN'=>1, 'OR'=>2, etc...] for easy lookup
        $account_types = AccountType::whereNotNull('dp_code')
            ->get()
            ->pluck('id', 'dp_code')
            ->toArray();

        // stats
        $total_to_process = $all_members->count();
        $failed = [];
        $processed = 0;
        $success = 0;

        // chunk records
        $all_members->orderBy('id')->chunk(250, function ($members) use (&$processed, &$success, &$failed, &$account_types) {
            // grab the donors for this chunk
            $donors = app('dpo')->table('dp')
                ->select([
                    'donor_id',
                    'title',
                    'email',
                    'last_name',
                    'address',
                    'address2',
                    'city',
                    'state',
                    'zip',
                    'country',
                    'org_rec',
                    'donor_type',
                ])->whereIn('donor_id', $members->pluck('donor_id'))
                ->get();

            // loop over this chunk
            foreach ($members as $member) {
                try {
                    // find the specific donor
                    $donor = $donors->filter(function ($d) use ($member) {
                        return $d->donor_id == $member->donor_id;
                    })->first();

                    // bail if on donor found in the batch we pulled
                    if (! $donor) {
                        throw new MessageException('Donor id not found.');
                    }

                    // title
                    if (in_array('donor_title', $this->options) && $donor->title) {
                        $member->title = $donor->title;
                        $member->bill_title = $donor->title;
                    }

                    // email
                    if (in_array('email', $this->options) && $donor->email) {
                        $member->ship_email = $donor->email;
                        $member->bill_email = $donor->email;

                        // do not set the primary email to an email that already exists in Givecloud
                        if (Member::where('id', '!=', $member->id)->where('email', $donor->email)->count() == 0) {
                            $member->email = $donor->email;
                        }
                    }

                    // organization name
                    if (in_array('organization_name', $this->options) && $donor->org_rec == 'Y' && $donor->last_name) {
                        $member->bill_organization_name = $donor->last_name;
                    }

                    // address
                    if (in_array('address', $this->options, true) && $donor->address) {
                        $member->bill_address_01 = $donor->address;
                        $member->bill_address_02 = $donor->address2;
                        $member->bill_city = $donor->city;
                        $member->bill_state = $donor->state;
                        $member->bill_zip = $donor->zip;
                        $member->bill_country = $donor->country;
                    }

                    // account/donor type
                    if (in_array('donor_type', $this->options) && $donor->donor_type) {
                        // check donor_type/account_type
                        if (! isset($account_types[$donor->donor_type])) {
                            throw new MessageException("Donor type ('" . $donor->donor_type . "') does not map to any account types in Givecloud.");
                        }

                        // set account type
                        $member->account_type_id = $account_types[$donor->donor_type];
                    }

                    // save member
                    $member->save();

                    $success++;
                } catch (\Exception $e) {
                    $failed[] = 'DP ID ' . $member->donor_id . ' (GC Name: ' . $member->display_name . ') failed. ' . $e->getMessage();
                }

                $processed++;
            }
        });

        $all_accounts = Member::count();
        $failed_count = count($failed);

        // optionally send email
        if (isset($this->options['notify_email'])) {
            $body = "Hey There!<br><br>We're done pulling DP donor data into your Givecloud account.<br><br>"
                . "<strong>{$total_to_process}</strong> of {$all_accounts} supporters in Givecloud had DonorPerfect IDs we could use.<br>"
                . "<strong>{$success}</strong> Givecloud supporters were updated.<br>"
                . (($failed_count > 0) ? "<strong>{$failed_count}</strong> Givecloud supporters couldn't be updated:<br>" : '')
                . (($failed_count > 0) ? implode('<br> - ', $failed) . '<br>' : '')
                . '<br>--<br>Givecloud Robot 🤖<br>' . config('mail.support.address') . ' | https://help.givecloud.com';

            $message = (new \Swift_Message)
                ->setFrom('notifications@givecloud.co', 'Givecloud Robot')
                ->setSubject('Donor Sync: Complete!')
                ->setTo($this->options['notify_email'])
                ->setCc(config('mail.support.address'))
                ->setBody($body, 'text/html');

            send_using_swiftmailer($message);
        }
    }
}
