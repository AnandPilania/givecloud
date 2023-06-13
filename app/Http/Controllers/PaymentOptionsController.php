<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Sponsorship\Models\PaymentOption;
use Ds\Domain\Sponsorship\Models\PaymentOptionGroup;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentOptionsController extends Controller
{
    public function index()
    {
        // permissions
        user()->canOrRedirect('paymentoption');

        // get all groups
        $payment_groups = PaymentOptionGroup::orderBy('name')
            // option count
            ->join(DB::raw('(SELECT group_id as __group_id, COUNT(*) AS option_count FROM payment_option WHERE is_deleted = 0 GROUP BY group_id) as option_counter'), 'option_counter.__group_id', '=', 'payment_option_group.id', 'left')
            // use count
            ->join(DB::raw('(SELECT sponsorship_payment_option_groups.payment_option_group_id as __payment_option_group_id, COUNT(*) AS use_count FROM sponsorship_payment_option_groups INNER JOIN sponsorship ON sponsorship.id = sponsorship_payment_option_groups.sponsorship_id WHERE sponsorship.is_deleted = 0 GROUP BY sponsorship_payment_option_groups.payment_option_group_id) as use_counter'), 'use_counter.__payment_option_group_id', '=', 'payment_option_group.id', 'left')
            ->get();

        // render view
        return $this->getView('payment_options/index', [
            '__menu' => 'sponsorship.payments',
            'pageTitle' => 'Payment Options',
            'payment_groups' => $payment_groups,
        ]);
    }

    public function view()
    {
        // permission
        user()->canOrRedirect('paymentoption');

        // edit group
        if (request()->filled('i')) {
            $payment_group = PaymentOptionGroup::withTrashed()->with('options')->where('id', request('i'))->first();
            $title = $payment_group->name;

        // new group
        } else {
            $payment_group = new PaymentOptionGroup;
            $title = 'Add Payment Option';
        }

        // render view
        return $this->getView('payment_options/view', [
            '__menu' => 'sponsorship.payments',
            'pageTitle' => $title,
            'payment_group' => $payment_group,
        ]);
    }

    public function destroy(Request $request)
    {
        // permission
        user()->canOrRedirect('paymentoption.edit');

        try {
            $paymentOptionId = (int) $request->id;
            PaymentOptionGroup::findOrFail($request->id)->delete();
        } catch (ModelNotFoundException $e) {
            $this->flash->error("Cannot find Payment Option #$paymentOptionId for deletion.");
        }

        // return to list
        return redirect()->route('backend.sponsorship.payment_options.index');
    }

    public function restore()
    {
        // permission
        user()->canOrRedirect('paymentoption.edit');

        // restore record
        $payment_group = PaymentOptionGroup::withTrashed()->findOrFail(request('i'));
        $payment_group->restore();

        // return to list
        return redirect()->to('jpanel/sponsorship/payment_options/edit?i=' . $payment_group->getKey());
    }

    public function save()
    {
        // permission
        user()->canOrRedirect('paymentoption.edit');

        // create record if it doesn't exist
        if (! request()->filled('id')) {
            $payment_group = new PaymentOptionGroup;
        } else {
            $payment_group = PaymentOptionGroup::find(request('id'));
        }

        // update group
        $payment_group->name = request('name');
        $payment_group->save();

        // save options
        foreach (request('payment_group_options') as $dom_id => $option) {
            // delete record
            if (isset($option['_isdelete']) && $option['_isdelete'] == '1') {
                $paymentOption = PaymentOption::findOrFail($option['id']);
                $paymentOption->delete();

                continue;
            }

            // insert record
            if ($option['_isnew'] == '1') {
                $paymentOption = new PaymentOption;
                $paymentOption->group_id = $payment_group->id;
                $paymentOption->created_at = now();
                $paymentOption->created_by = user('id');
                $paymentOption->updated_at = now();
                $paymentOption->updated_by = user('id');
                $paymentOption->save();
            } else {
                $paymentOption = PaymentOption::findOrFail($option['id']);
            }

            // update record
            $paymentOption->is_recurring = $option['is_recurring'];
            $paymentOption->is_custom = $option['is_custom'];
            $paymentOption->sequence = $option['sequence'];
            $paymentOption->recurring_frequency = ($option['is_recurring'] == '1') ? $option['recurring_frequency'] : null;
            $paymentOption->recurring_day = ($option['is_recurring'] == '1') ? $option['recurring_day'] : null;
            $paymentOption->recurring_day_of_week = ($option['is_recurring'] == '1') ? $option['recurring_day_of_week'] : null;
            $paymentOption->amount = ($option['is_custom'] == '0') ? $option['amount'] : null;
            $paymentOption->save();
        }

        // loop through all the options and add them
        return redirect()->to('jpanel/sponsorship/payment_options');
    }
}
