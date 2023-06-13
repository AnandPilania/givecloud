<?php

namespace Ds\Jobs\Import;

use Ds\Models\AccountType;
use Ds\Models\Member;
use Ds\Models\Membership;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class SupportersFromFile extends ImportJob
{
    /** @var array */
    protected $analyzedRows;

    /**
     * Version of the import process.
     */
    public function getColumnDefinitions(): Collection
    {
        /*
        'id' => 'order_number',
        [
            'validator' => 'nullable|max:45',
            'name' => 'Contribution Number',
            'hint' => 'The unique number associated with this contribution.',
            'sanitize' => null,
            'validator' => 'required|max:48|alpha_num|exists:productorder,invoicenumber',
            'messages' => [
                'order_number.exists' => 'Contribution (:value) already exists.'
            ],
            'custom_validator' = function ($row){
                return true;
            }
        ],
        */

        $headers = collect([]);

        $headers->push((object) [
            'id' => 'first_name',
            'name' => 'First Name',
            'validator' => 'nullable|max:45',
            'hint' => 'The supporter\'s first name.',
        ]);

        $headers->push((object) [
            'id' => 'last_name',
            'name' => 'Last Name',
            'validator' => 'nullable|max:45',
            'hint' => 'Recommended. The supporter\'s last name.',
        ]);

        $headers->push((object) [
            'id' => 'bill_organization_name',
            'name' => 'Organization Name',
            'validator' => 'nullable|max:100',
            'hint' => 'Recommended. The supporter\'s organization name.',
        ]);

        $account_type_names = AccountType::select('name')->get()->pluck('name')->all();

        $headers->push((object) [
            'id' => 'account_type',
            'name' => 'Supporter Type',
            'validator' => [
                'nullable',
                Rule::in($account_type_names),
            ],
            'hint' => 'Recommended. Must be exactly one of: ' . implode(', ', $account_type_names) . '.',
            'default' => AccountType::select('name')->default()->first()->name ?? '',
        ]);

        $headers->push((object) [
            'id' => 'referral_source',
            'name' => 'Referral Source',
            'validator' => [
                'nullable',
                'max:45',
            ],
            'hint' => 'How this supporter heard about your organization.',
        ]);

        $headers->push((object) [
            'id' => 'email',
            'name' => 'Email',
            'validator' => 'nullable|email|max:45',
            'hint' => 'Recommended. MUST BE UNIQUE. The supporter\'s email address. If this is not provided, this individual will not be able to login to your website. Supporters with duplicate emails will be merged.',
        ]);

        $headers->push((object) [
            'id' => 'password',
            'name' => 'Password',
            'validator' => 'nullable|max:45',
            'hint' => 'If provided, Givecloud will set this as the supporter\'s password. Password must be provided in clear-text (not hashed or encrypyted).  Givecloud will secure the password.',
        ]);

        $headers->push((object) [
            'id' => 'bill_title',
            'name' => 'Billing Title',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default billing title (Mr / Ms / Mrs, etc).',
        ]);

        $headers->push((object) [
            'id' => 'bill_first_name',
            'name' => 'Billing First Name',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default billing first name.',
        ]);

        $headers->push((object) [
            'id' => 'bill_last_name',
            'name' => 'Billing Last Name',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default billing last name.',
        ]);

        $headers->push((object) [
            'id' => 'bill_address_01',
            'name' => 'Billing Address Line 1',
            'validator' => 'nullable|max:450',
            'hint' => 'Supporter billing address line 1.',
        ]);

        $headers->push((object) [
            'id' => 'bill_address_02',
            'name' => 'Billing Address Line 2',
            'validator' => 'nullable|max:450',
            'hint' => 'Supporter default billing address line 2.',
        ]);

        $headers->push((object) [
            'id' => 'bill_city',
            'name' => 'Billing City',
            'validator' => 'nullable|max:200',
            'hint' => 'Supporter default billing city.',
        ]);

        $headers->push((object) [
            'id' => 'bill_state',
            'name' => 'Billing Province / State',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default billing state or province.',
        ]);

        $headers->push((object) [
            'id' => 'bill_zip',
            'name' => 'Billing Postal Code / ZIP',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default billing postal code or ZIP.',
        ]);

        $headers->push((object) [
            'id' => 'bill_country',
            'name' => 'Billing Country',
            'validator' => 'nullable|max:2',
            'hint' => 'Supporter default billing country. Must be the two digit ISO-A2 abbreviation.',
        ]);

        $headers->push((object) [
            'id' => 'bill_email',
            'name' => 'Billing Email',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default billing email.',
        ]);

        $headers->push((object) [
            'id' => 'bill_phone',
            'name' => 'Billing Phone',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default billing phone.',
        ]);

        $headers->push((object) [
            'id' => 'ship_title',
            'name' => 'Shipping Title',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default shipping title (Mr / Ms / Mrs, etc).',
        ]);

        $headers->push((object) [
            'id' => 'ship_first_name',
            'name' => 'Shipping First Name',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default shipping first name.',
        ]);

        $headers->push((object) [
            'id' => 'ship_last_name',
            'name' => 'Shipping Last Name',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default shipping last name.',
        ]);

        $headers->push((object) [
            'id' => 'ship_address_01',
            'name' => 'Shipping Address Line 1',
            'validator' => 'nullable|max:450',
            'hint' => 'Supporter default shipping address line 1.',
        ]);

        $headers->push((object) [
            'id' => 'ship_address_02',
            'name' => 'Shipping Address Line 2',
            'validator' => 'nullable|max:450',
            'hint' => 'Supporter default shipping address line 2.',
        ]);

        $headers->push((object) [
            'id' => 'ship_city',
            'name' => 'Shipping City',
            'validator' => 'nullable|max:200',
            'hint' => 'Supporter default shipping city.',
        ]);

        $headers->push((object) [
            'id' => 'ship_state',
            'name' => 'Shipping Province / State',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default shipping state or province.',
        ]);

        $headers->push((object) [
            'id' => 'ship_zip',
            'name' => 'Shipping Postal Code / ZIP',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default shipping postal code or ZIP.',
        ]);

        $headers->push((object) [
            'id' => 'ship_country',
            'name' => 'Shipping Country',
            'validator' => 'nullable|max:2',
            'hint' => 'Supporter default shipping country. Must be the two digit ISO-A2 abbreviation.',
        ]);

        $headers->push((object) [
            'id' => 'ship_email',
            'name' => 'Shipping Email',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default shipping email.',
        ]);

        $headers->push((object) [
            'id' => 'ship_phone',
            'name' => 'Shipping Phone',
            'validator' => 'nullable|max:45',
            'hint' => 'Supporter default shipping phone.',
        ]);

        $headers->push((object) [
            'id' => 'created_at',
            'name' => 'Created Date/Time',
            'validator' => 'nullable|date',
            'hint' => 'The date/time this donor was original created or first recorded in your local time zone.',
        ]);

        $headers->push((object) [
            'id' => 'donor_id',
            'name' => 'Donor ID',
            'validator' => 'nullable|max:45',
            'hint' => 'The donor ID reference in your existing CRM that matches this supporter.',
        ]);

        $memberships = Membership::all()->pluck('name')->all();

        $headers->push((object) [
            'id' => 'membership_name',
            'name' => 'Membership Level',
            'validator' => [
                'nullable',
                Rule::in($memberships),
            ],
            'hint' => 'If this supporter belongs to a membership level, this column must contain exact name of the membership level.' . ((count($memberships) > 0) ? ' Must be exactly one of: ' . implode(', ', $memberships) . '.' : ' Currently, you have no membership levels configured.'),
        ]);

        $headers->push((object) [
            'id' => 'membership_starts_on',
            'name' => 'Membership Start Date',
            'validator' => 'nullable|max:45',
            'hint' => 'The date the membership started. Must be formatted YYYY-MM-DD or MM/DD/YYYY. Try to be consistent with the format you choose.',
        ]);

        $headers->push((object) [
            'id' => 'membership_expires_on',
            'name' => 'Membership Expiry Date',
            'validator' => 'nullable|max:45',
            'hint' => 'The date the membership level expires. Must be formatted YYYY-MM-DD or MM/DD/YYYY. Try to be consistent with the format you choose.',
        ]);

        return $headers;
    }

    /**
     * Execute the analysis.
     *
     * @return void
     */
    public function handleAnalysis()
    {
        $this->analyzedRows = [];

        return parent::handleAnalysis();
    }

    /**
     * Analyze a row.
     *
     * @param array $row
     */
    public function analyzeRow(array $row)
    {
        $messages = [];

        if (array_get($row, 'email')) {
            if (Member::where('email', $row['email'])->count()) {
                $messages[] = sprintf('Will merge with an existing supporter with the email "%s".', $row['email']);
            }

            $index = array_search($row['email'], $this->analyzedRows);
            if ($index !== false) {
                $messages[] = sprintf('Duplicate of Row %d using email "%s".', $index + 2, $row['email']);
            }
        }

        if (Arr::get($row, 'membership_name')) {
            if (! Membership::select('id')->where('name', $row['membership_name'])->count()) {
                $messages[] = sprintf('There is no membership with the name "%s".', $row['membership_name']);
            }
        }

        $this->analyzedRows[] = $row['email'] ?? null;

        return implode('', $messages) ?: null;
    }

    /**
     * Import a row.
     *
     * @param array $row
     */
    public function importRow(array $row)
    {
        $member_data = [
            'sign_up_method' => 'import',
            'created_by' => $this->import->created_by,
            'updated_by' => $this->import->created_by,
            'created_at' => fromUtcFormat('now', 'datetime'),
            'updated_at' => fromUtcFormat('now', 'datetime'),
        ];

        // look up a member w/ the same email OR create a new member w/ the same email
        if ($row['email']) {
            $member = Member::where('email', $row['email'])->first();
            if ($member) {
                $member_data = [
                    'id' => $member->id,
                    'updated_by' => $this->import->created_by,
                    'updated_at' => fromUtcFormat('now', 'datetime'),
                ];
            }
        }

        // loop over all column headers in the order we are expecting them
        foreach ($this->getColumnDefinitions() as $column) {
            // if there is no value in this cell, skip it
            if (! isset($row[$column->id])) {
                continue;
            }

            // get a cleaned up version of the cell
            $cell_value = $row[$column->id];

            if ($column->id == 'membership_name') {
                $membership = Membership::select('id')->where('name', $cell_value)->first();
            } elseif ($column->id == 'account_type') {
                $accountType = AccountType::select('id', 'is_organization')->where('name', $cell_value)->first();
                $member_data['account_type_id'] = $accountType->id;
            } elseif ($column->id == 'password') {
                $member_data[$column->id] = $cell_value ? bcrypt($cell_value) : null;
            } elseif ($column->id == 'created_at') {
                $member_data[$column->id] = toUtcFormat($cell_value, 'datetime') ?? fromUtcFormat('now', 'datetime');
            } elseif ($column->id == 'membership_starts_on') {
                $membership_starts_on = toUtcFormat($cell_value, 'date');
            } elseif ($column->id == 'membership_expires_on') {
                $membership_expires_on = toUtcFormat($cell_value, 'date');
            } else {
                $member_data[$column->id] = $cell_value;
            }
        }

        if ($accountType->is_organization ?? false) {
            $member_data['display_name'] = $member_data['bill_organization_name'] ?? null;
        } else {
            $member_data['display_name'] = trim(($member_data['first_name'] ?? '') . ' ' . ($member_data['last_name'] ?? ''));
        }

        // if merging an existing member
        if (isset($member)) {
            $this->import->importMessage("Row {$this->import->current_record} with email '" . $row['email'] . "' was merged with existing supporter '" . trim($member->getOriginal('first_name') . ' ' . $member->getOriginal('last_name')) . "' ('" . $member->id . "').");

            $member_id = $member_data['id'];
            unset($member_data['id']);
            Member::where('id', $member_id)->update($member_data);
            $op = 'updated_records';
        } else {
            $id = Member::insertGetId($member_data);
            $member = Member::find($id);
            $op = 'added_records';
        }

        if (isset($membership)) {
            $groupAccount = $member->addUniqueGroup($membership, $membership_starts_on ?? $member->created_at);

            if (isset($membership_expires_on)) {
                $groupAccount->end_date = $membership_expires_on;
                $groupAccount->save();
            }
        }

        return $op;
    }
}
