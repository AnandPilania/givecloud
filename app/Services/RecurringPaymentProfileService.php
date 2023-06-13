<?php

namespace Ds\Services;

use Ds\Domain\Shared\Date;
use Ds\Enums\BillingPeriod;
use Ds\Models\RecurringPaymentProfile;

class RecurringPaymentProfileService
{
    public function getFirstPossibleStartDate(
        RecurringPaymentProfile $rpp,
        string $type,
        bool $initialCharge = false,
        ?Date $delayUntilAfterDate = null,
        ?Date $startDate = null
    ): Date {
        $startDate = ($startDate ?? fromLocal('today')->asDate())->copy();
        $earliestDate = $startDate->copy();
        $delayUntilAfterDate = ($delayUntilAfterDate ?? $earliestDate)->copy();

        if ($this->shouldAddBillingIntervalToEarliestDate($rpp, $type, $initialCharge)) {
            $this->addBillingIntervalToDate($rpp, $earliestDate);
        }

        $this->snapDateToBillingCycleAnchor($rpp, $startDate);

        while ($startDate->lt($earliestDate) || $startDate->lte($delayUntilAfterDate)) {
            $this->addBaseBillingIntervalToDate($rpp, $startDate);
            $this->snapDateToBillingCycleAnchor($rpp, $startDate);
        }

        return $startDate;
    }

    private function shouldAddBillingIntervalToEarliestDate(RecurringPaymentProfile $rpp, string $type, bool $initialCharge): bool
    {
        if ($type === 'natural') {
            return true;
        }

        $snapTo = sys_get("rpp_start_date_snap_{$rpp->billing_period}");

        if ($snapTo === 'donor' && $initialCharge) {
            return true;
        }

        return false;
    }

    public function getNextPossibleBillingDate(RecurringPaymentProfile $rpp, ?Date $fromDate = null): Date
    {
        $billingDate = $this->getPossibleBillingDate($rpp, $fromDate);

        $this->addBillingIntervalToDate($rpp, $billingDate);
        $this->snapDateToBillingCycleAnchor($rpp, $billingDate);

        if ($billingDate->isPast()) {
            return $this->getNextPossibleBillingDate($rpp, $billingDate);
        }

        return $billingDate;
    }

    public function getSoonestPossibleBillingDate(RecurringPaymentProfile $rpp, ?Date $fromDate = null): Date
    {
        $billingDate = $this->getPossibleBillingDate($rpp, $fromDate);

        $this->addBaseBillingIntervalToDate($rpp, $billingDate);
        $this->snapDateToBillingCycleAnchor($rpp, $billingDate);

        if ($billingDate->isPast()) {
            return $this->getSoonestPossibleBillingDate($rpp, $billingDate);
        }

        return $billingDate;
    }

    private function getPossibleBillingDate(RecurringPaymentProfile $rpp, ?Date $fromDate = null): Date
    {
        $billingDate = $fromDate
            ?? $rpp->next_billing_date
            ?? $rpp->profile_start_date
            ?? fromLocal('today')->asDate();

        return $billingDate->copy();
    }

    public function addBillingIntervalToDate(RecurringPaymentProfile $rpp, Date $date): void
    {
        switch ($rpp->billing_period) {
            case BillingPeriod::DAY:
                $date->addDay();
                break;
            case BillingPeriod::WEEK:
                $date->addWeek();
                break;
            case BillingPeriod::SEMI_MONTH:
                $date->addWeeks(2);
                break;
            case BillingPeriod::MONTH:
                $date->addMonthWithoutOverflow();
                break;
            case BillingPeriod::QUARTER:
                $date->addMonthsWithoutOverflow(3);
                break;
            case BillingPeriod::SEMI_YEAR:
                $date->addMonthsWithoutOverflow(6);
                break;
            case BillingPeriod::YEAR:
                $date->addYearWithNoOverflow();
                break;
            default: // do nothing
        }
    }

    public function addBaseBillingIntervalToDate(RecurringPaymentProfile $rpp, Date $date): void
    {
        switch ($rpp->billing_period) {
            case BillingPeriod::DAY:
                $date->addDay();
                break;
            case BillingPeriod::WEEK:
            case BillingPeriod::SEMI_MONTH:
                $date->addWeek();
                break;
            case BillingPeriod::MONTH:
            case BillingPeriod::QUARTER:
            case BillingPeriod::SEMI_YEAR:
            case BillingPeriod::YEAR:
                $date->addMonthWithoutOverflow();
                break;
            default: // do nothing
        }
    }

    public function subBillingIntervalFromDate(RecurringPaymentProfile $rpp, Date $date): void
    {
        switch ($rpp->billing_period) {
            case BillingPeriod::DAY:
                $date->subDay();
                break;
            case BillingPeriod::WEEK:
                $date->subWeek();
                break;
            case BillingPeriod::SEMI_MONTH:
                $date->subWeeks(2);
                break;
            case BillingPeriod::MONTH:
                $date->subMonthWithoutOverflow();
                break;
            case BillingPeriod::QUARTER:
                $date->subMonthsWithoutOverflow(3);
                break;
            case BillingPeriod::SEMI_YEAR:
                $date->subMonthsWithoutOverflow(6);
                break;
            case BillingPeriod::YEAR:
                $date->subYearWithNoOverflow();
                break;
            default: // do nothing
        }
    }

    private function snapDateToBillingCycleAnchor(RecurringPaymentProfile $rpp, Date $date): void
    {
        switch ($rpp->billing_period) {
            case BillingPeriod::WEEK:
            case BillingPeriod::SEMI_MONTH:
                $this->snapDateToADayOfWeekBillingCycleAnchor($rpp, $date);
                break;
            case BillingPeriod::MONTH:
            case BillingPeriod::QUARTER:
            case BillingPeriod::SEMI_YEAR:
            case BillingPeriod::YEAR:
                $this->snapDateToADayOfMonthBillingCycleAnchor($rpp, $date);
                break;
            default: // do nothing
        }
    }

    private function snapDateToADayOfMonthBillingCycleAnchor(RecurringPaymentProfile $rpp, Date $date): void
    {
        if (! $rpp->billing_cycle_anchor) {
            return;
        }

        $dayOfMonth = (int) $rpp->billing_cycle_anchor->format('j');
        $lastDayOfMonth = (int) $date->copy()->endOfMonth()->format('j');

        if ($lastDayOfMonth < $dayOfMonth) {
            $dayOfMonth = $lastDayOfMonth;
        }

        $date->day($dayOfMonth);
    }

    private function snapDateToADayOfWeekBillingCycleAnchor(RecurringPaymentProfile $rpp, Date $date): void
    {
        if (! $rpp->billing_cycle_anchor) {
            return;
        }

        $dayOfWeek = (int) $rpp->billing_cycle_anchor->format('w');

        $date->subWeek()->endOfWeek()->next($dayOfWeek);
    }
}
