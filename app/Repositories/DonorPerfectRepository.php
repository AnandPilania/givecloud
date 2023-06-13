<?php

namespace Ds\Repositories;

use Ds\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DonorPerfectRepository
{
    public static function importDonors($opts = [])
    {
        $opts = array_merge([
            'create_login' => false,
            'import_pledges' => false, // <<== we CAN NOT do this ... pledges have GL codes... how would we import those?  we'd have to link it to a product and an order
        ], $opts);

        $log = [];

        $total = 0;
        $total_matches = 0;
        $total_ignored = 0;
        $total_new = 0;
        $total_temp_logins = 0;

        // grab all donors from dp
        foreach (dpo_request('SELECT * FROM dp ORDER BY donor_id ASC') as $donor) {
            $total++;

            // ///////////////////////////////////////////////////////////
            // ///////////////////////////////////////////////////////////
            // do we have a matching donor by email?
            if ($member = Member::where(DB::raw('trim(lower(email))'), '=', trim(strtolower($donor->email)))
                ->first()) {
                if ($member->donor_id) {
                    $log[] = toUtcFormat('now', 'H:i:s') . ' [IGNORED] Matching supporter (' . $member->id . ') found for email ' . $donor->email . ' (dp donor id: ' . $donor->donor_id . ') but the supporter is already linked to donor id ' . $member->donor_id . '.';
                    $total_ignored++;

                    continue;
                }

                $log[] = toUtcFormat('now', 'H:i:s') . ' Matching supporter (' . $member->id . ') found for email ' . $donor->email . ' (dp donor id: ' . $donor->donor_id . ').';
                $member->donor_id = $donor->id;
                $total_matches++;

            // ///////////////////////////////////////////////////////////
            // ///////////////////////////////////////////////////////////
            // do we have a matching donor by first name, last name, postal?
            } elseif ($member = Member::where(DB::raw('trim(lower(first_name))'), 'LIKE', '%' . trim(strtolower($donor->first_name)))
                ->where(DB::raw('trim(lower(last_name))'), 'LIKE', '%' . trim(strtolower($donor->last_name)))
                ->where(DB::raw('trim(lower(bill_zip))'), 'LIKE', '%' . trim(strtolower($donor->zip)))
                ->first()) {
                if ($member->donor_id) {
                    $log[] = toUtcFormat('now', 'H:i:s') . ' [IGNORED] Matching supporter (' . $member->id . ') found for ' . $donor->first_name . ' ' . $donor->last_name . ' at ZIP ' . $donor->zip . ' (dp donor id: ' . $donor->donor_id . ') but the supporter is already linked to donor id ' . $member->donor_id . '.';
                    $total_ignored++;

                    continue;
                }

                $log[] = toUtcFormat('now', 'H:i:s') . ' Matching supporter (' . $member->id . ') found for ' . $donor->first_name . ' ' . $donor->last_name . ' at ZIP ' . $donor->zip . ' (dp donor id: ' . $donor->donor_id . ').';
                $member->donor_id = $donor->id;
                $total_matches++;

            // ///////////////////////////////////////////////////////////
            // ///////////////////////////////////////////////////////////
            // create a new supporter
            } else {
                $log[] = toUtcFormat('now', 'H:i:s') . ' New supporter will be created for donor ' . $donor->first_name . ' ' . $donor->last_name . ' at ZIP ' . $donor->zip . ' (dp donor id: ' . $donor->donor_id . ').';
                $member = new Member;
                $member->donor_id = $donor->donor_id;
                $member->sign_up_method = 'import';
                $member->first_name = $donor->first_name;
                $member->last_name = $donor->last_name;
                $member->email = $donor->email;
                $member->ship_first_name = $donor->first_name;
                $member->ship_last_name = $donor->last_name;
                $member->ship_email = $donor->email;
                $member->bill_first_name = $donor->first_name;
                $member->bill_last_name = $donor->last_name;
                $member->bill_email = $donor->email;
                $member->bill_address_01 = $donor->address;
                $member->bill_address_02 = $donor->address2;
                $member->bill_city = $donor->city;
                $member->bill_state = $donor->state;
                $member->bill_zip = $donor->zip;
                $member->bill_country = $donor->country;
                $member->bill_phone = (trim($member->mobile_phone) !== '') ? $member->mobile_phone : $member->home_phone;
                $total_new++;
            }

            // create a temp password
            if ($opts['create_login']) {
                if (trim($member->email) !== '' && trim($member->password) === '') {
                    $temp_password = Str::random(12);
                    $member->password = $temp_password;
                    $member->force_password_reset = true;
                    $total_temp_logins++;
                    $log[] = toLocalFormat('now', 'H:i:s') . '           Temp login created using ' . $member->email . '.';
                } else {
                    if (trim($member->email) === '') {
                        $log[] = toUtcFormat('now', 'H:i:s') . '           [NO LOGIN CREATED] No email address.';
                    } elseif (trim($member->password) !== '') {
                        $log[] = toUtcFormat('now', 'H:i:s') . '           [NO LOGIN CREATED] Login already exists using.';
                    }
                }
            }

            // save member
            $member->save();
        }

        $body = 'Hi ' . user('firstname') . ',<br><br>Thanks for your patience.<br><br>Great news! We just finished importing <strong>' . number_format($total) . ' donors</strong> into GC from DonorPerfect.<br><br>' .
            '<strong>' . number_format($total_matches) . '</strong> matches were found and connected in GC.<br>' .
            '<strong>' . number_format($total_ignored) . '</strong> matches were found and IGNORED in GC.<br>' .
            '<strong>' . number_format($total_new) . '</strong> new supporters were created in GC.<br>' .
            '<strong>' . number_format($total_temp_logins) . '</strong> temporary logins were created.<br><br>Here\'s exactly what we did in painful detail :)<br><br><pre>' . implode(chr(10), $log) . '</pre>';

        $message = (new \Swift_Message)
            ->setFrom(config('mail.support.address'), config('mail.support.name'))
            ->addTo(user('email'), user('full_name'))
            ->setSubject('Donor Import Complete!')
            ->setBody($body, 'text/html');

        send_using_swiftmailer($message);
    }
}
