<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Mail\ContributionsDailyDigest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UtilityController extends Controller
{
    /**
     * A function to run every time this controller is used.
     */
    public function __construct()
    {
        parent::__construct();

        // require super user access for all end-points in this controller
        $this->middleware('requires.superUser');
    }

    /**
     * Utilities home screen.
     *
     * @return \Illuminate\View\View
     */
    public function view()
    {
        return $this->getView('utilities/index');
    }

    /**
     * Show unreceipted orders/txns
     *
     * @return \Illuminate\View\View
     */
    public function show_unreceipted()
    {
        $unreceipted = collect();

        // all receiptable products
        \Ds\Models\Product::receiptable()->get()->each(function ($product) use (&$unreceipted) {
            // unreceipted orders (> $0, in THIS COUNTRY, in THIS YEAR)
            $order_ids = collect(DB::select("SELECT count(o.id) as counter
                FROM product as p
                INNER JOIN productinventory as v on v.productid = p.id
                INNER JOIN productorderitem as i on i.productinventoryid = v.id
                INNER JOIN productorder as o on o.id = i.productorderid
                LEFT JOIN tax_receipts as t on t.order_id = o.id
                WHERE p.id = ? and o.totalamount > 0 and o.billingcountry = ? and year(convert_tz(o.confirmationdatetime, 'UTC', ?)) = ? and t.id is null;", [$product->id, sys_get('tax_receipt_country'), localOffset(), request()->input('year')]))->first()->counter;

            // unreceipted txns (> $0, in THIS COUNTRY, in THIS YEAR)
            $txn_ids = collect(DB::select("SELECT count(tx.id) as counter
                FROM product as p
                INNER JOIN productinventory as v on v.productid = p.id
                INNER JOIN productorderitem as i on i.productinventoryid = v.id
                INNER JOIN productorder as o on o.id = i.productorderid
                INNER JOIN recurring_payment_profiles as rpp on rpp.productorderitem_id = i.id
                INNER JOIN transactions tx on tx.recurring_payment_profile_id = rpp.id
                LEFT JOIN tax_receipts as t on t.transaction_id = tx.id
                WHERE p.id = ? and o.billingcountry = ? and year(convert_tz(tx.order_time, 'UTC', ?)) = ? and t.id is null;", [$product->id, sys_get('tax_receipt_country'), localOffset(), request()->input('year')]))->first()->counter;

            // skip if none found
            if (! $order_ids && ! $txn_ids) {
                return;
            }

            $unreceipted->push((object) [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_code' => $product->code,
                'affected_orders' => (int) $order_ids,
                'affected_txns' => (int) $txn_ids,
                'affected_total' => ($order_ids + $txn_ids),
            ]);
        });

        return view('utilities.show_unreceipted', [
            'unreceipted' => $unreceipted,
            'year' => request()->input('year'),
        ]);
    }

    /**
     * Fix Tax Receipts
     *
     * @return \Illuminate\View\View
     */
    public function process_receipts()
    {
        set_time_limit(60 * 10);

        $year = request()->input('year');
        $product_id = (int) request()->input('product');

        $orders = collect(DB::select("SELECT o.id
            FROM product as p
            INNER JOIN productinventory as v on v.productid = p.id
            INNER JOIN productorderitem as i on i.productinventoryid = v.id
            INNER JOIN productorder as o on o.id = i.productorderid
            LEFT JOIN tax_receipts as t on t.order_id = o.id
            WHERE p.id = ? and o.totalamount > 0 and o.billingcountry = ? and year(convert_tz(o.confirmationdatetime, 'UTC', ?)) = ? and t.id is null;", [$product_id, sys_get('tax_receipt_country'), localOffset(), request()->input('year')]));

        $txns = collect(DB::select("SELECT tx.id
            FROM product as p
            INNER JOIN productinventory as v on v.productid = p.id
            INNER JOIN productorderitem as i on i.productinventoryid = v.id
            INNER JOIN productorder as o on o.id = i.productorderid
            INNER JOIN recurring_payment_profiles as rpp on rpp.productorderitem_id = i.id
            INNER JOIN transactions tx on tx.recurring_payment_profile_id = rpp.id
            LEFT JOIN tax_receipts as t on t.transaction_id = tx.id
            WHERE p.id = ? and o.billingcountry = ? and year(convert_tz(tx.order_time, 'UTC', ?)) = ? and t.id is null;", [$product_id, sys_get('tax_receipt_country'), localOffset(), request()->input('year')]));

        if ($orders->count() === 0
            && $txns->count() === 0) {
            throw new MessageException('No contributions or transactions found to process for product (' . $product_id . ') in (' . $year . ').');
        }

        echo '<style>body {background-color:#222; color:#eee; font-family:monospace;} a {color:#bbf;}</style>';

        echo date('G:i:s') . ' - Processing (' . $orders->count() . ') contributions + (' . $txns->count() . ') txns = (' . ($orders->count() + $txns->count()) . ') total<br>';

        // all order related tax receipts
        foreach ($orders as $order) {
            // generate the tax receipt with NO NOTIFICATION
            try {
                $rcpt = \Ds\Models\TaxReceipt::createFromOrder($order->id, false);
                echo date('G:i:s') . ' - ' . $rcpt->number . ' created from contribution ' . $order->id . '<br>';
            } catch (\Exception $e) {
                echo date('G:i:s') . ' - ' . $e->getMessage() . ' (contribution ' . $order->id . ')<br>';
            }
        }

        // all transaction related tax receipts
        foreach ($txns as $txn) {
            // generate the tax receipt with NO NOTIFICATION
            try {
                $rcpt = \Ds\Models\TaxReceipt::createFromTransaction($txn->id, false);
                echo date('G:i:s') . ' - ' . $rcpt->number . ' created from contribution ' . $txn->id . '<br>';
            } catch (\Exception $e) {
                echo date('G:i:s') . ' - ' . $e->getMessage() . ' (txn ' . $txn->id . ')<br>';
            }
        }

        echo date('G:i:s') . ' - Done. <a href="/jpanel/utilities/show_unreceipted?year=' . $year . '">Back to report.</a><br>';
        exit;
    }

    public function dp_gifts_missing_pay_methods()
    {
        // the list of gifts we will build
        $gifts = [];

        // run custom sql on dp server
        $response = dpo_request('SELECT D.DONOR_ID,
                D.FIRST_NAME,
                D.LAST_NAME,
                G.AMOUNT,
                G.GIFT_DATE,
                G.GIFT_ID,
                G.VAULT_ID,
                P.DPPAYMENTMETHODID,
                P.CUSTOMERVAULTID,
                G.RECORD_TYPE,
                G.CREATED_BY,
                G.GIFT_NARRATIVE
            FROM DP D JOIN DPGIFT G ON D.DONOR_ID=G.DONOR_ID
            LEFT JOIN DPPAYMENTMETHOD P ON G.DONOR_ID=P.DONOR_ID
            WHERE G.VAULT_ID IS NOT NULL
                AND P.DPPAYMENTMETHODID IS NULL
                AND G.CREATED_BY=?', [sys_get('dpo_user_alias')]);

        if ($response === null) {
            return $this->getView('utilities/index');
        }

        // loop over each record
        for ($i = 0; $i < count($response); $i++) {
            // build the gift entry
            $g = (object) [
                'GIFT_ID' => $response[$i]->GIFT_ID,
                'FIRST_NAME' => $response[$i]->FIRST_NAME,
                'LAST_NAME' => $response[$i]->LAST_NAME,
                'GIFT_DATE' => $response[$i]->GIFT_DATE,
                'VAULT_ID' => $response[$i]->VAULT_ID,
                'GIFT_NARRATIVE' => $response[$i]->GIFT_NARRATIVE,
                'DPPAYMENTMETHODID' => $response[$i]->DPPAYMENTMETHODID,
                'CUSTOMERVAULTID' => $response[$i]->CUSTOMERVAULTID,
                'RECORD_TYPE' => $response[$i]->RECORD_TYPE,
                'CREATED_BY' => $response[$i]->CREATED_BY,
                'CART_ID' => null,
            ];

            // find the ds order
            $cart_id = db_var("SELECT id FROM productorder WHERE vault_id = '%s'", $g->VAULT_ID);
            if (is_numeric($cart_id)) {
                $g->CART_ID = (int) $cart_id;
            }

            // add to the list of gifts
            $gifts[] = $g;
        }

        return $this->getView('utilities/dp_gifts_missing_pay_methods', compact('gifts'));
    }

    public function sync_unsynced_orders()
    {
        // extend timeout so it has time to process all contributions
        set_time_limit(60 * 60 * 30); // 30 minutes

        $orders = \Ds\Models\Order::paid()->unsynced()->select('invoicenumber', 'client_uuid')->get();

        echo count($orders) . ' to sync:<br/><br/>';

        foreach ($orders as $order) {
            echo '- syncing ' . $order->invoicenumber . ' (' . $order->client_uuid . ')... ';

            try {
                app('Ds\Services\DonorPerfectService')->pushOrder($order);
            } catch (\Exception $e) {
                echo '!! ERROR !! ' . $e->getMessage() . '<br />';

                continue;
            }

            echo 'SUCCESS!' . '<br />';
        }
    }

    public function sync_unsynced_txns()
    {
        dispatch(new \Ds\Jobs\CommitTransactionsToDPO);

        $this->flash->success(sprintf(
            'Dispatched commit transactions job. %s will be notified when its complete.',
            config('mail.support.address')
        ));

        return redirect()->back();
    }

    public function all_pledges()
    {
        $all_pledges = app('dpo')->table('dpgift')
            ->join('dpgiftudf', 'dpgiftudf.gift_id', '=', 'dpgift.gift_id')
            ->join('dp', 'dp.donor_id', '=', 'dpgift.donor_id')
            ->leftJoin('dppaymentmethod', function ($query) {
                $query->on('dppaymentmethod.donor_id', '=', 'dpgift.donor_id')
                    ->where('dppaymentmethod.isdefault', '=', true);
            })->select('dpgift.donor_id', 'dp.first_name', 'dp.last_name', 'dp.email', 'dpgift.gift_id', 'dpgiftudf.eft', 'dpgift.bill', 'dpgift.frequency', 'dpgift.start_date', 'dpgift.last_paid_date', 'dpgift.gl_code', 'dpgift.solicit_code', 'dpgift.sub_solicit_code', 'dpgift.campaign', 'dpgift.gift_type', 'dpgift.gift_narrative', 'dppaymentmethod.CustomerVaultID')
            ->where('dpgift.record_type', '=', 'P')
            ->orderBy('dpgift.donor_id');

        header('Content-type: text/csv');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="' . export_filename('all_pledges.csv') . '"');
        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, ['Donor ID', 'First Name', 'Last Name', 'Email', 'Pledge ID', 'Is EFT (Auto)', 'Amount', 'Frequency', 'Start Date', 'Last Billed On', 'GL Code', 'Solicit', 'Sub-Solicit', 'Campaign', 'Gift Type', 'Narrative', 'Vault ID', 'GC ID']);

        $all_pledges->chunk(500, function ($pledges) use ($outstream) {
            $accounts = \Ds\Models\Member::where('is_active', true)->whereIn('donor_id', $pledges->pluck('donor_id'))->get();

            $pledges->each(function ($pledge) use ($outstream, $accounts) {
                $account = $accounts->filter(function ($a) use ($pledge) {
                    return $a->donor_id == $pledge->donor_id;
                })->first();

                // remove the extra column added by
                // query builder for doing the chunking
                if (isset($pledge->row_num)) {
                    unset($pledge->row_num);
                }

                $data = array_values((array) $pledge);
                $data[] = $account->id ?? '';
                fputcsv($outstream, (array) $data);
            });

            unset($accounts);
        });

        exit;
    }

    public function importable_pledges()
    {
        return response()->streamDownload(function () {
            // list of all members in givecloud
            $members = \Ds\Models\Member::where('is_active', true)->whereNotNull('donor_id');

            // write to file
            $outstream = fopen('php://output', 'w');

            fputcsv($outstream, ['Active', 'GC ID', 'Donor ID', 'First Name', 'Last Name', 'Email', 'Pledge ID', 'Is EFT (Auto)', 'Amount', 'Frequency', 'Start Date', 'Last Billed On', 'GL Code', 'Solicit', 'Sub-Solicit', 'Campaign', 'Gift Type', 'Narrative', 'Vault ID', 'Child Reference Number', 'Product Code']);

            // loop over each member
            $members->orderBy('id')->chunk(200, function ($member_chunk) use (&$outstream) {
                // find the donor in DP
                $donors = app('dpo')->table('dp')
                    ->select('donor_id', 'first_name', 'last_name', 'email')
                    ->whereIn('donor_id', $member_chunk->pluck('donor_id'))
                    ->orderBy('donor_id')
                    ->get();

                $pledges = app('dpo')->table('dpgift')
                    ->join('dpgiftudf', 'dpgiftudf.gift_id', '=', 'dpgift.gift_id')
                    ->select('dpgift.donor_id', 'dpgift.gift_id', 'dpgift.bill', 'dpgift.frequency', 'dpgift.start_date', 'dpgift.last_paid_date', 'dpgift.gl_code', 'dpgift.solicit_code', 'dpgift.sub_solicit_code', 'dpgift.campaign', 'dpgift.gift_type', 'dpgift.gift_narrative', 'dpgiftudf.eft')
                    ->whereIn('dpgift.donor_id', $member_chunk->pluck('donor_id'))
                    ->where('dpgift.record_type', '=', 'P')
                    ->orderBy('dpgift.donor_id')
                    ->get();

                $payment_methods = app('dpo')->table('dppaymentmethod')
                    ->select('donor_id', 'CustomerVaultID')
                    ->whereIn('donor_id', $member_chunk->pluck('donor_id'))
                    ->where('isdefault', '=', true)
                    ->orderBy('donor_id')
                    ->get();

                foreach ($member_chunk as $member) {
                    $donor = $donors->filter(function ($d) use ($member) {
                        return $d->donor_id == $member->donor_id;
                    })->first();

                    if (! $donor) {
                        continue;
                    }

                    $donor->pledges = $pledges->filter(function ($p) use ($member) {
                        return $p->donor_id == $member->donor_id;
                    });

                    if (! $donor->pledges) {
                        continue;
                    }

                    $donor->payment_method = $payment_methods->filter(function ($m) use ($member) {
                        return $m->donor_id == $member->donor_id;
                    })->first();

                    // loop over each pledge and add it to the output
                    foreach ($donor->pledges as $pledge) {
                        $start_date = fromLocal($pledge->start_date);

                        $active = $start_date && $start_date->isPast()
                            && ($pledge->total_paid < $pledge->total || $pledge->total == 0);

                        fputcsv($outstream, [
                            $active ? 'Y' : 'N',
                            $member->id,
                            $donor->donor_id,
                            $member->first_name,
                            $member->last_name,
                            $member->email,
                            $pledge->gift_id,
                            $pledge->eft,
                            $pledge->bill,
                            $pledge->frequency,
                            $pledge->start_date,
                            $pledge->last_paid_date,
                            $pledge->gl_code,
                            $pledge->solicit_code,
                            $pledge->sub_solicit_code,
                            $pledge->campaign,
                            $pledge->gift_type,
                            $pledge->gift_narrative,
                            ($donor->payment_method->customervaultid ?? null),
                            null,
                            null,
                        ]);
                    }
                }
            });

            fclose($outstream);
        }, date('YmdHis') . '-importable-pledges.csv', [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description' => 'File Transfer',
            'Content-type' => 'text/csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ]);
    }

    public function orders_without_adjustments()
    {
        header('Content-type: text/plain');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="' . export_filename('orders_without_adjustments.csv') . '"');

        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, [
            'Contribution',
            'Contribution Date',
            'Contribution Total',
            'Full Refund',
            'Refunded Amt',
            'Refunded Date',
            'Gift ID',
            'Gift Amount',
            'Donor',
            'Edit Gift',
        ], ',', '"');

        \Ds\Models\Order::withSpam()->refunded()->chunk(50, function ($order_chunk) use ($outstream) {
            $gift_ids = [];
            foreach ($order_chunk as $order) {
                $gift_ids = array_merge($gift_ids, explode(',', $order->alt_transaction_id));
            }
            $gift_ids = array_map('intval', $gift_ids);

            $gifts = app('dpo')->table('dpgift')->whereIn('gift_id', $gift_ids)->get();

            foreach ($order_chunk as $order) {
                $gifts->filter(function ($g) use ($order) {
                    return in_array($g->gift_id, explode(',', $order->alt_transaction_id));
                })->each(function ($go) use ($order, $outstream) {
                    if ($go->record_type != 'A') {
                        fputcsv($outstream, [
                            $order->invoicenumber,
                            toLocalFormat($order->createddatetime, 'M j, Y'),
                            number_format($order->totalamount, 2),
                            ($order->refunded_amt == $order->totalamount) ? 'Y' : 'N',
                            number_format($order->refunded_amt, 2),
                            fromLocal($order->refunded_at, 'M j, Y'),
                            $go->gift_id,
                            number_format($go->amount, 2),
                            $go->donor_id,
                            'https://www.donorperfect.net/prod/ScreenDesigner/Gift/Edit/' . $go->gift_id,
                        ], ',', '"');
                    }
                });
            }
        });

        exit;
    }

    public function preview_daily_digest()
    {
        $date = fromLocal(request('date', 'today'))->asDate();
        $recipient = request('to');

        $mailable = new ContributionsDailyDigest($date, user());

        if ($recipient) {
            $mailable->to($recipient);
            $mailable->from('notifications@givecloud.co', 'Givecloud');

            Mail::send($mailable);
        }

        return $mailable;
    }
}
