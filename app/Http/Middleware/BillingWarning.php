<?php

namespace Ds\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Ds\Repositories\ChargebeeRepository;
use Illuminate\Http\Response;

class BillingWarning
{
    /** @var \Ds\Repositories\ChargebeeRepository */
    private $chargebeeRepo;

    /**
     * @param \Ds\Repositories\ChargebeeRepository $chargebeeRepo
     */
    public function __construct(ChargebeeRepository $chargebeeRepo)
    {
        $this->chargebeeRepo = $chargebeeRepo;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (
            $this->isSuperUserInProduction()
            || $this->isDevelopmentClient()
            || $this->isGuest()
            || $this->isFrontendRoute()
            || $this->isAnExemptRoute($request)
            || $this->warningIsSuppressed()
            || app(ChargebeeRepository::class)->hasNoPastDueBalance()
        ) {
            return $next($request);
        }

        return $this->renderBillingProblem();
    }

    /**
     * renders the Billing Problem
     *
     * @return \Illuminate\Http\Response
     */
    private function renderBillingProblem()
    {
        return new Response(
            view('sessions.billing_problem', [
                'balance' => $this->chargebeeRepo->getBalance(),
                'balance_currency_code' => $this->chargebeeRepo->getCustomer()->preferredCurrencyCode,
            ])
        );
    }

    /**
     * Checks if they are a super user in production
     *
     * @return bool
     */
    private function isSuperUserInProduction()
    {
        return (! isDev() && is_super_user()) ? true : false;
    }

    /**
     * Checks if site belongs to a development client.
     *
     * @return bool
     */
    private function isDevelopmentClient()
    {
        return site()->client->is_development;
    }

    /**
     * Checks if they are a guest (not logged in)
     *
     * @return bool
     */
    private function isGuest()
    {
        return (! auth()->user()) ? true : false;
    }

    /**
     * Checks if the route is a front-end route
     *
     * @return bool
     */
    private function isFrontendRoute()
    {
        return (! is_jpanel_route()) ? true : false;
    }

    /**
     * Checks if the route is exempt from the billing warning
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function isAnExemptRoute($request)
    {
        return (
            $request->is('*/billing*', '*/chargebee*', '*/pos*', '*/onboard*', '*/logout', '*/invoice/*')
            || $request->ajax()
            || $request->wantsJson()
        ) ? true : false;
    }

    /**
     * Checks if the warning is suppressed
     *
     * @return bool
     */
    private function warningIsSuppressed()
    {
        return (
            ! empty(auth()->user()->billing_warning_suppression_expiry_date)
            && auth()->user()->billing_warning_suppression_expiry_date >= Carbon::now()
        ) ? true : false;
    }
}
