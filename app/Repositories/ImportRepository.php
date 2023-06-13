<?php

namespace Ds\Repositories;

use Ds\Domain\Sponsorship\Models\Segment;
use Ds\Models\Membership;

class ImportRepository
{
    /**
     * Get the column headers for an import type
     *
     * @return \Illuminate\Support\Collection
     */
    public function getHeaders($type)
    {
        if ($type === 'accounts_file') {
            return $this->getAccountsFileHeaders();
        }

        if ($type === 'sponsorship_file') {
            return $this->getSponsorshipFileHeaders();
        }

        if ($type === 'rpps_file') {
            return $this->getRecurringPaymentProfilesFileHeaders();
        }

        return collect();
    }

    /**
     * Get the column headers for the accounts file import type
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAccountsFileHeaders()
    {
        static $headers;

        if ($headers) {
            return $headers;
        }

        $headers = collect();

        $headers->push((object) [
            'name' => 'First Name',
            'column' => 'first_name',
            'hint' => 'Required. The supporter\'s first name.',
        ]);

        $headers->push((object) [
            'name' => 'Last Name',
            'column' => 'last_name',
            'hint' => 'Recommended. The supporter\'s last name.',
        ]);

        $headers->push((object) [
            'name' => 'Email',
            'column' => 'email',
            'hint' => 'Recommended. The supporter\'s email address. If this is not provided, this individual will not be able to login to your website.',
        ]);

        $headers->push((object) [
            'name' => 'Password',
            'column' => 'password',
            'hint' => 'Optional. If provided, GC will set this as the supporter\'s password. Password must be provided in clear-text (not hashed or encrypyted).  GC will secure the password.',
        ]);

        $headers->push((object) [
            'name' => 'Billing First Name',
            'column' => 'bill_first_name',
            'hint' => 'Optional. Supporter default billing first name.',
        ]);

        $headers->push((object) [
            'name' => 'Billing Last Name',
            'column' => 'bill_last_name',
            'hint' => 'Optional. Supporter default billing last name.',
        ]);

        $headers->push((object) [
            'name' => 'Billing Address Line 1',
            'column' => 'bill_address_01',
            'hint' => 'Optional. Supporter default billing address line 1.',
        ]);

        $headers->push((object) [
            'name' => 'Billing Address Line 2',
            'column' => 'bill_address_02',
            'hint' => 'Optional. Supporter default billing address line 2.',
        ]);

        $headers->push((object) [
            'name' => 'Billing City',
            'column' => 'bill_city',
            'hint' => 'Optional. Supporter default billing city.',
        ]);

        $headers->push((object) [
            'name' => 'Billing Province / State',
            'column' => 'bill_state',
            'hint' => 'Optional. Supporter default billing state or province.',
        ]);

        $headers->push((object) [
            'name' => 'Billing Postal Code / ZIP',
            'column' => 'bill_zip',
            'hint' => 'Optional. Supporter default billing postal code or ZIP.',
        ]);

        $headers->push((object) [
            'name' => 'Billing Country',
            'column' => 'bill_country',
            'hint' => 'Optional. Supporter default billing country. Must be the two digit ISO-A2 abbreviation.',
        ]);

        $headers->push((object) [
            'name' => 'Billing Email',
            'column' => 'bill_email',
            'hint' => 'Optional. Supporter default billing email.',
        ]);

        $headers->push((object) [
            'name' => 'Billing Phone',
            'column' => 'bill_phone',
            'hint' => 'Optional. Supporter default billing phone.',
        ]);

        $headers->push((object) [
            'name' => 'Shipping First Name',
            'column' => 'ship_first_name',
            'hint' => 'Optional. Supporter default shipping first name.',
        ]);

        $headers->push((object) [
            'name' => 'Shipping Last Name',
            'column' => 'ship_last_name',
            'hint' => 'Optional. Supporter default shipping last name.',
        ]);

        $headers->push((object) [
            'name' => 'Shipping Address Line 1',
            'column' => 'ship_address_01',
            'hint' => 'Optional. Supporter default shipping address line 1.',
        ]);

        $headers->push((object) [
            'name' => 'Shipping Address Line 2',
            'column' => 'ship_address_02',
            'hint' => 'Optional. Supporter default shipping address line 2.',
        ]);

        $headers->push((object) [
            'name' => 'Shipping City',
            'column' => 'ship_city',
            'hint' => 'Optional. Supporter default shipping city.',
        ]);

        $headers->push((object) [
            'name' => 'Shipping Province / State',
            'column' => 'ship_state',
            'hint' => 'Optional. Supporter default shipping state or province.',
        ]);

        $headers->push((object) [
            'name' => 'Shipping Postal Code / ZIP',
            'column' => 'ship_zip',
            'hint' => 'Optional. Supporter default shipping postal code or ZIP.',
        ]);

        $headers->push((object) [
            'name' => 'Shipping Country',
            'column' => 'ship_country',
            'hint' => 'Optional. Supporter default shipping country. Must be the two digit ISO-A2 abbreviation.',
        ]);

        $headers->push((object) [
            'name' => 'Shipping Email',
            'column' => 'ship_email',
            'hint' => 'Optional. Supporter default shipping email.',
        ]);

        $headers->push((object) [
            'name' => 'Shipping Phone',
            'column' => 'ship_phone',
            'hint' => 'Optional. Supporter default shipping phone.',
        ]);

        $headers->push((object) [
            'name' => 'Donor ID',
            'column' => 'donor_id',
            'hint' => 'Optional. The donor ID reference in your existing CRM that matches this supporter.',
        ]);

        $memberships = Membership::all()->pluck('name')->toArray();

        $headers->push((object) [
            'name' => 'Membership Level',
            'column' => 'membership.name',
            'hint' => 'Optional. If this supporter belongs to a membership level, this column must contain exact name of the membership level as defined in GC.' . ((count($memberships) > 0) ? ' Must be one of: ' . implode(', ', $memberships) . '.' : ' Currently, you have no membership levels configured in GC.'),
        ]);

        $headers->push((object) [
            'name' => 'Membership Expiry Date',
            'column' => 'membership_expires_on',
            'hint' => 'Optional. The date the membership level expires. Must be formatted YYYY-MM-DD or MM/DD/YYYY. Try to be consistent with the format you choose.',
        ]);

        $headers->push((object) [
            'name' => 'Sponsorships',
            'column' => 'sponsorship.reference_number',
            'hint' => 'Optional. A list of sponsorship reference numbers that this supporter sponsors. The list must be separated by either a comma or a carriage return.',
        ]);

        return $headers;
    }

    /**
     * Get the column headers for the sponsorship file import type
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSponsorshipFileHeaders()
    {
        static $headers;

        if ($headers) {
            return $headers;
        }

        $headers = collect();

        $headers->push((object) [
            'name' => 'Reference Number',
            'column' => 'reference_number',
            'hint' => 'Reqired. A unique number that identifies this record.',
        ]);

        $headers->push((object) [
            'name' => 'First Name',
            'column' => 'first_name',
            'hint' => 'Required.',
        ]);

        $headers->push((object) [
            'name' => 'Last Name',
            'column' => 'last_name',
            'hint' => 'Optional.',
        ]);

        $headers->push((object) [
            'name' => 'Gender',
            'column' => 'gender',
            'hint' => 'Required. Must be the value \'M\' or \'F\'',
        ]);

        $headers->push((object) [
            'name' => 'Birth Date',
            'column' => 'birth_date',
            'hint' => 'Optional. Must be formatted YYYY-MM-DD or MM/DD/YYYY. Try to be consistent with the format you choose.',
        ]);

        $headers->push((object) [
            'name' => 'Private Note',
            'column' => 'private_notes',
            'hint' => 'Optional. A private note related to this record that only staff can view.',
        ]);

        $headers->push((object) [
            'name' => 'Page Content/Biography',
            'column' => 'biography',
            'hint' => 'Optional. HTML formatted content for viewing on website.',
        ]);

        $headers->push((object) [
            'name' => 'Is Sponsored',
            'column' => 'is_sponsored',
            'hint' => 'Optional. Must be the value \'Y\' or \'N\'. If blank, GC will automatically determine the status of the sponsorship based on linked sponsors.',
        ]);

        $headers->push((object) [
            'name' => 'Display on Website',
            'column' => 'is_enabled',
            'hint' => 'Optional. Must be the value \'Y\' or \'N\'. If blank, GC assumes N.',
            'default' => 'N',
        ]);

        $headers->push((object) [
            'name' => 'Latitude',
            'column' => 'latitude',
            'hint' => 'Optional. The location coordinate for where the child is located.',
        ]);

        $headers->push((object) [
            'name' => 'Longitude',
            'column' => 'longitude',
            'hint' => 'Optional. The location coordinate for where the child is located.',
        ]);

        $headers->push((object) [
            'name' => 'GL Code',
            'column' => 'meta1',
            'hint' => 'Optional. General ledger code for accounting and reporting.',
        ]);

        // if DP is integrated, show DP columns
        if (dpo_is_enabled()) {
            // three basic DP fields
            $headers->push((object) [
                'name' => 'Campaign',
                'column' => 'meta2',
                'hint' => 'Optional. Campaign code for accounting and reporting.',
            ]);

            $headers->push((object) [
                'name' => 'Solicitation',
                'column' => 'meta3',
                'hint' => 'Optional. Solicitation code for accounting and reporting.',
            ]);

            $headers->push((object) [
                'name' => 'Sub Solicitation',
                'column' => 'meta4',
                'hint' => 'Optional. Sub solicitation code for accounting and reporting.',
            ]);

            // custom dp fields
            for ($ix = 9; $ix <= 23; $ix++) {
                // skip blank records
                if (trim(sys_get('dp_meta' . $ix . '_label')) == '') {
                    continue;
                }

                // add each custom dp record
                $headers->push((object) [
                    'name' => sys_get('dp_meta' . $ix . '_label'),
                    'column' => 'meta' . $ix,
                    'hint' => 'Optional. DonorPerfect User-Defined Field.',
                ]);
            }
        }

        // loop over custom segments and add each segment record
        foreach (Segment::active()->orderBy('id')->with('items')->get() as $segment) {
            $headers->push((object) [
                'name' => $segment->name,
                'column' => 'segment.' . $segment->id,
                'hint' => 'Optional. ' . (($segment->show_in_detail) ? ' Public. ' : ' Private. ') . $segment->description . (($segment->items->count() > 0) ? ' Must be one of: \'' . implode('\' or \'', $segment->items->pluck('name')->toArray()) . '\'' : ''),
            ]);
        }

        return $headers;
    }

    /**
     * Get the column headers for the recurring payment profile import type
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRecurringPaymentProfilesFileHeaders()
    {
        static $headers;

        if ($headers) {
            return $headers;
        }

        $headers = collect();

        $headers->push((object) [
            'name' => 'Donor ID',
            'column' => 'donor_id',
            'hint' => 'Reqired.',
        ]);

        $headers->push((object) [
            'name' => 'Amount',
            'column' => 'amount',
            'hint' => 'Reqired.',
        ]);

        $headers->push((object) [
            'name' => 'Frequency',
            'column' => 'frequency',
            'hint' => 'Reqired. Must be one of W, BW, M, Q, A (Weekly, Semi-Monthly, Monthly, Quarterly, Yearly)',
        ]);

        $headers->push((object) [
            'name' => 'Start Date',
            'column' => 'start_date',
            'hint' => 'Reqired. Must be formatted YYYY-MM-DD or MM/DD/YYYY. Try to be consistent with the format you choose.',
        ]);

        $headers->push((object) [
            'name' => 'Vault ID',
            'column' => 'vault_id',
            'hint' => 'Reqired.',
        ]);

        $headers->push((object) [
            'name' => 'Child Reference Number',
            'column' => 'child_reference_number',
            'hint' => 'Optional. Either Child Reference Number or Product Code MUST EXISTS. Identifies which sponsorship record in your Givecloud account this recurring payment relates to.',
        ]);

        $headers->push((object) [
            'name' => 'Product Code',
            'column' => 'product_code',
            'hint' => 'Optional. Either Child Reference Number or Product Code MUST EXISTS. Identifies which item in your Givecloud account this recurring payment relates to.',
        ]);

        return $headers;
    }
}
