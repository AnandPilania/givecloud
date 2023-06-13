<?php

namespace Ds\Http\Controllers;

use Ds\Services\DonorPerfectService;
use Throwable;

class DonorController extends Controller
{
    public function gift()
    {
        $gift = app('Ds\Services\DonorPerfectService')->gift(request('id'));

        if ($gift) {
            $gift->donor = app('Ds\Services\DonorPerfectService')->donor($gift->donor_id);
        }

        $this->setViewLayout(false);

        return $this->getView('donors/gift', compact('gift'));
    }

    public function view()
    {
        $donors = dpo_request(sprintf(
            'SELECT d.donor_id,
                d.first_name,
                d.last_name,
                d.middle_name,
                d.suffix,
                d.title,
                d.salutation,
                d.prof_title,
                d.opt_line,
                d.address,
                d.address2,
                d.city,
                d.state,
                d.zip,
                d.country,
                d.address_type,
                d.home_phone,
                d.business_phone,
                d.fax_phone,
                d.mobile_phone,
                d.email,
                d.org_rec,
                d.donor_type,
                d.narrative,
                d.tag_date,
                d.initial_gift_date,
                d.last_contrib_date,
                d.ytd,
                d.created_by,
                d.created_date,
                d.modified_by,
                d.modified_date,
                d.donor_rcpt_type,
                d.receipt_delivery
            FROM dp d
            WHERE d.donor_id = %d
            ORDER BY d.last_name, d.first_name',
            (int) request('id')
        ));

        $donor = $donors[0];

        $pledges = dpo_request(sprintf(
            "SELECT TOP 100
                g.gift_id,
                g.gl_code,
                g.amount,
                g.gift_date,
                g.solicit_code,
                g.sub_solicit_code,
                g.campaign,
                g.reference,
                g.gift_narrative,
                g.record_type,
                g.nocalc,
                g.rcpt_type,
                g.gift_type,
                g.start_date,
                g.bill,
                g.frequency
            FROM dpgift g
            WHERE g.donor_id = %d AND record_type = 'P'
            ORDER BY g.gift_date DESC, g.reference",
            (int) $donor->donor_id
        ));

        $gifts = dpo_request(sprintf(
            "SELECT TOP 20
                g.gift_id,
                g.gl_code,
                g.amount,
                g.gift_date,
                g.solicit_code,
                g.sub_solicit_code,
                g.campaign,
                g.reference,
                g.gift_narrative,
                g.record_type,
                g.nocalc,
                g.rcpt_type,
                g.gift_type
            FROM dpgift g
            WHERE g.donor_id = %d AND record_type = 'G'
            ORDER BY g.gift_date DESC, g.reference",
            (int) $donor->donor_id
        ));

        if (request('first_name') || request('last_name') || request('email')) {
            $donor_matches = dpo_request(sprintf(
                "SELECT TOP 20
                    d.donor_id,
                    d.first_name,
                    d.last_name,
                    d.address,
                    d.city,
                    d.state,
                    d.zip,
                    d.country,
                    d.email,
                    SUM(g.amount) as gift_total,
                    COUNT(g.gift_id) as gift_count
                FROM dp d
                LEFT JOIN dpgift g ON g.donor_id = d.donor_id AND g.record_type = 'G'
                WHERE d.donor_id != %d
                    AND (d.first_name LIKE '%%%s%%'
                        OR d.last_name LIKE '%%%s%%'
                        OR (isnull(d.email,'') != '' AND d.email LIKE '%%%s%%'))
                GROUP BY d.donor_id, d.first_name, d.last_name, d.address, d.city, d.state, d.zip, d.country, d.email",
                (int) $donor->donor_id,
                app('dpo.client')->escape(request('first_name')),
                app('dpo.client')->escape(request('last_name')),
                app('dpo.client')->escape(request('email'))
            ));
        } else {
            $donor_matches = null;
        }

        $this->setViewLayout(false);

        return $this->getView('donors/view', compact('donors', 'donor', 'pledges', 'gifts', 'donor_matches'));
    }

    public function getCodes($fieldName)
    {
        if (! dpo_is_enabled()) {
            return [];
        }

        $key = "dp-codes:$fieldName";
        $cache = app('cache')->tags('dp-codes');

        if ($cache->has($key)) {
            return $cache->get($key);
        }

        try {
            return $cache->remember($key, now()->addHours(6), function () use ($fieldName) {
                return app(DonorPerfectService::class)->getCodes($fieldName);
            });
        } catch (Throwable $e) {
            return [];
        }
    }

    public function clearCache()
    {
        app('cache')->tags('dp-codes')->flush();

        return response()->json(true);
    }

    public function getDonor(DonorPerfectService $dpService, $donorId)
    {
        $donor = $dpService->donor($donorId);

        if ($donor) {
            $donor->preview = "$donor->title $donor->first_name $donor->last_name<br>";
            $donor->preview .= address_format(
                $donor->address,
                $donor->address2,
                $donor->city,
                $donor->state,
                $donor->zip,
                $donor->country,
                '<br>'
            );

            if ($donor->email) {
                $donor->preview .= "<br>{$donor->email}";
            }

            if ($donor->home_phone) {
                $donor->preview .= "<br>{$donor->home_phone}";
            }

            $udfs = $dpService->getDonorUdfs($donorId);

            if (! empty($udfs->mcat)) {
                $donor->preview .= sprintf(
                    "\n\n<strong>Membership:</strong> %s (expires: %s)",
                    $this->getCodes('MCAT')[$udfs->mcat] ?? $udfs->mcat,
                    $udfs->mcat_expire_date ?: 'n/a'
                );
            }

            $donor->preview = nl2br(trim($donor->preview));

            return response()->json($donor);
        }

        abort(404);
    }

    public function verifyConnection()
    {
        app('dpo.client')->setLogin(request('username'));
        app('dpo.client')->setPassword(request('password'));
        app('dpo.client')->setApiKey(request('apikey'));

        return response()->json(app('Ds\Services\DonorPerfectService')->ping(true));
    }

    public function import()
    {
        set_time_limit(60 * 20); // 20 minutes

        \Ds\Repositories\DonorPerfectRepository::importDonors([
            'create_login' => (request('create_login') == 1),
        ]);

        return response()->json(true);
    }
}
