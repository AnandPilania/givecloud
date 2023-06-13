<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\Member;

class RecurringPaymentsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member');
    }

    public function index()
    {
        pageSetup(__('frontend/accounts.subscriptions.index.my_preapproved_payments'), 'content');

        $recurringPaymentProfiles = Member::find(member('id'))->recurringPaymentProfiles()
            ->orderByRaw("FIELD(status,'SUSPENDED','ACTIVE','EXPIRED','CANCELLED') ASC, profile_start_date DESC")
            ->get();

        return $this->renderTemplate('accounts/subscriptions/index', [
            'subscriptions' => $recurringPaymentProfiles,
        ]);
    }

    public function view($profileId)
    {
        pageSetup(__('frontend/accounts.subscriptions.view.recurring_payment_details'), 'content');

        $recurringPaymentProfile = Member::find(member('id'))
            ->recurringPaymentProfiles()
            ->hashid($profileId)
            ->first();

        if (! $recurringPaymentProfile) {
            abort(404);
        }

        return $this->renderTemplate('accounts/subscriptions/view', [
            'subscription' => $recurringPaymentProfile,
        ]);
    }

    public function cancel($profileId)
    {
        pageSetup(__('frontend/accounts.subscriptions.cancel.cancel_my_recurring_payment'));

        $recurringPaymentProfile = Member::find(member('id'))
            ->recurringPaymentProfiles()
            ->hashid($profileId)
            ->first();

        if (! $recurringPaymentProfile) {
            abort(404);
        }

        return $this->renderTemplate('accounts/subscriptions/cancel', [
            'subscription' => $recurringPaymentProfile,
        ]);
    }

    public function edit($profileId)
    {
        $member = member();

        $paymentMethods = $member->paymentMethods()
            ->active()
            ->get();

        $recurringPaymentProfile = $member->recurringPaymentProfiles()
            ->hashid($profileId)
            ->first();

        if (! $recurringPaymentProfile) {
            abort(404);
        }

        return $this->renderTemplate('accounts/subscriptions/edit', [
            'payment_methods' => Drop::collectionFactory($paymentMethods, 'PaymentMethod'),
            'subscription' => $recurringPaymentProfile,
        ]);
    }
}
