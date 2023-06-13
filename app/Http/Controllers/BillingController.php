<?php

namespace Ds\Http\Controllers;

use Carbon\Carbon;
use Ds\Common\Chargebee\BillingPlansService;
use Ds\Domain\MissionControl\MissionControlService;
use Ds\Repositories\ChargebeeRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class BillingController extends Controller
{
    public function index(ChargebeeRepository $chargebeeRepo): View
    {
        user()->canOrRedirect('admin.billing');

        $shouldShowPlans = site('direct_billing_enabled') && $chargebeeRepo->getSubscription() === null;

        $fromDonorPerfectWithoutSubscription = optional(site()->partner)->identifier === 'dp'
            && site()->subscription === null
            && $chargebeeRepo->getCustomer();

        return view('settings/billing/index', [
            'shouldShowPlans' => $shouldShowPlans || request()->get('plans'),
            'shouldShowCurrentBillingScreen' => ! $fromDonorPerfectWithoutSubscription && ! $shouldShowPlans,
            'fromDonorPerfectWithoutSubscription' => $fromDonorPerfectWithoutSubscription,
            'plans' => app(BillingPlansService::class)->all(),
            'cb_customer' => $chargebeeRepo->getCustomer(),
            'cb_subscription' => $chargebeeRepo->getSubscription(),
            'cb_card' => $chargebeeRepo->getPaymentMethod(),
            'cb_plan' => $chargebeeRepo->getPlan(),
        ]);
    }

    public function save(): RedirectResponse
    {
        user()->canOrRedirect('admin.billing');

        if (sys_set('billing_pays_by_cheque', (bool) request('billing_pays_by_cheque'))) {
            $this->flash->success('Billing settings successfully updated!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        return redirect()->to('jpanel/settings/billing');
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function redirectToCustomerPortal()
    {
        user()->canOrRedirect('admin.billing');

        try {
            $res = app('chargebee')->createPortalSession(site()->client->customer_id, url('jpanel'));
        } catch (Throwable $e) {
            abort(404);
        }

        return $res->getValues();
    }

    public function setOverdueReminder(MissionControlService $missioncontrol)
    {
        $user = auth()->user();

        $message = "Past Due Payment Reminder Dismissal: Reminder\n\n" . $user->fullname . ' chose to be reminded in 2 days.';
        $missioncontrol->addNote($message);

        $user->billing_warning_suppression_expiry_date = Carbon::now()->addDays(2);
        $user->save();

        $this->flash->success('You will be reminded in 2 days!');

        // go back to the page that the warning was displayed on
        return redirect()->back();
    }

    public function markOverdueAsAlreadyPaid(MissionControlService $missioncontrol)
    {
        $user = auth()->user();

        $message = "Past Due Payment Reminder Dismissal: Already Paid\n\n" . $user->fullname . ' indicated that the invoice should have already been paid.';
        $missioncontrol->addNote($message);

        $user->billing_warning_suppression_expiry_date = Carbon::now()->addDays(30);
        $user->save();

        $this->flash->success("We'll look into your payment!");

        // go back to the page that the warning was displayed on
        return redirect()->back();
    }

    public function flagOtherUserForOverdueAmount(MissionControlService $missioncontrol)
    {
        $user = auth()->user();

        $message = "Past Due Payment Reminder Dismissal: Wrong Person\n\n" . $user->fullname . ' indicated that they are not the right person for billing issues.';
        $missioncontrol->addNote($message);

        $user->billing_warning_suppression_expiry_date = Carbon::now()->addDays(60);
        $user->save();

        $this->flash->success('Thanks for letting us know!');

        // go back to the page that the warning was displayed on
        return redirect()->back();
    }
}
