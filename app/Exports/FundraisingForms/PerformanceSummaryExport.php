<?php

namespace Ds\Exports\FundraisingForms;

use Ds\Domain\Analytics\Builders\FundraisingForms\ContributionsBuilder;
use Ds\Domain\Analytics\Builders\FundraisingForms\ContributionsMedianBuilder;
use Ds\Domain\Analytics\Builders\FundraisingForms\EngagedVisitsBuilder;
use Ds\Domain\Analytics\Builders\FundraisingForms\ViewsBuilder;
use Ds\Models\Product;
use Maatwebsite\Excel\Concerns\FromArray;

class PerformanceSummaryExport implements FromArray
{
    /** @var \Ds\Models\Product */
    private $fundraisingForm;

    public function __construct(Product $fundraisingForm)
    {
        $this->fundraisingForm = $fundraisingForm;
    }

    public function array(): array
    {
        $currency = (string) currency();

        $rows = [
            [''],
            ['Views'],
            ['Engagement'],
            ['Engagement Rate (%)'],
            ['Contributions'],
            ['Engaged Conversion Rate (%)'],
            ['Overall Conversion Rate (%)'],
            ["Total Contribution Revenue ($currency)"],
            ['One-Time Contributions'],
            ["One-Time Revenue ($currency)"],
            ['One-Time Conversion Rate (%)'],
            ['Recurring Contributions'],
            ["Recurring Revenue ($currency)"],
            ['Recurring Conversion Rate (%)'],
            ["Contribution Revenue per View ($currency)"],
            ["Average Contribution Amount ($currency)"],
            ["Smallest Contribution Amount ($currency)"],
            ["Largest Contribution Amount ($currency)"],
            ["Median Contribution Amount ($currency)"],
            ["DCC Revenue ($currency)"],
            ["DCC Average Amount ($currency)"],
            ['DCC Coverage (%)'],
            ['DCC Opt-In'],
            ['DCC Opt-In Rate (%)'],
            ["Upsell Revenue ($currency)"],
            ['Upsell Opt-In'],
            ['Upsell Opt-In Rate (%)'],
            ['Marketing Opt-In'],
            ['Marketing Opt-In Rate (%)'],
            ['Employer Matching Opt-In'],
            ['Employer Matching Opt-In Rate (%)'],
        ];

        $days = min(120, (int) request('days', 60));
        $sourceFilter = in_array(request('type'), ['inline_embed', 'hosted_page', 'modal_embed'], true) ? request('type') : null;

        $endDate = toLocal('now')->endOfDay();
        $startDate = toLocal($endDate)->subDays($days - 1)->startOfDay();

        if (request('include_median')) {
            $contributionMedian = (new ContributionsMedianBuilder)
                ->setFundraisingForm($this->fundraisingForm)
                ->setSourceFilter($sourceFilter)
                ->setDateRange($startDate, $endDate)
                ->get();
        }

        $contributionStats = (new ContributionsBuilder)
            ->setFundraisingForm($this->fundraisingForm)
            ->setSourceFilter($sourceFilter)
            ->setDateRange($startDate, $endDate)
            ->get();

        $engagedVisitsStats = (new EngagedVisitsBuilder)
            ->setFundraisingForm($this->fundraisingForm)
            ->setDateRange($startDate, $endDate)
            ->get();

        $viewsStats = (new ViewsBuilder)
            ->setFundraisingForm($this->fundraisingForm)
            ->setDateRange($startDate, $endDate)
            ->get();

        while ($startDate->lte($endDate)) {
            $key = $startDate->format('Y-m-d');

            $views = $sourceFilter ? $viewsStats[$key]->{"{$sourceFilter}_views"} ?? '' : $viewsStats[$key]->total_views ?? '';
            $engagedViews = $sourceFilter ? $engagedVisitsStats[$key]->{"{$sourceFilter}_engaged_visits"} ?? '' : $engagedVisitsStats[$key]->total_engaged_visits ?? '';

            $data = [
                $startDate->format('M j'),
                $views,
                $engagedViews,
                round(rescueQuietly(fn () => $engagedViews / $views * 100), 1),
                $contributionStats[$key]->contribution_count ?? '',
                round(rescueQuietly(fn () => $contributionStats[$key]->contribution_count / $engagedViews * 100), 1),
                round(rescueQuietly(fn () => $contributionStats[$key]->contribution_count / $views * 100), 1),
                $contributionStats[$key]->contribution_revenue ?? '',
                $contributionStats[$key]->onetime_contribution_count ?? '',
                $contributionStats[$key]->onetime_contribution_revenue ?? '',
                round(rescueQuietly(fn () => $contributionStats[$key]->onetime_contribution_count / $views * 100), 1),
                $contributionStats[$key]->recurring_contribution_count ?? '',
                $contributionStats[$key]->recurring_contribution_revenue ?? '',
                round(rescueQuietly(fn () => $contributionStats[$key]->recurring_contribution_count / $views * 100), 1),
                round(rescueQuietly(fn () => $contributionStats[$key]->contribution_revenue / $views), 2),
                $contributionStats[$key]->contribution_average ?? '',
                $contributionStats[$key]->contribution_smallest ?? '',
                $contributionStats[$key]->contribution_largest ?? '',
                $contributionMedian[$key]->contribution_median ?? '',
                $contributionStats[$key]->dcc_revenue ?? '',
                $contributionStats[$key]->dcc_average ?? '',
                $contributionStats[$key]->dcc_coverage ?? '',
                $contributionStats[$key]->dcc_optin_count ?? '',
                $contributionStats[$key]->dcc_optin_conversion ?? '',
                $contributionStats[$key]->upsell_revenue ?? '',
                $contributionStats[$key]->upsell_optin_count ?? '',
                $contributionStats[$key]->upsell_optin_conversion ?? '',
                $contributionStats[$key]->email_optin_count ?? '',
                $contributionStats[$key]->email_optin_conversion ?? '',
                $contributionStats[$key]->employer_matching_optin_count ?? '',
                $contributionStats[$key]->employer_matching_optin_conversion ?? '',
            ];

            foreach (array_keys($rows) as $index) {
                $rows[$index][] = $data[$index] ?? '';
            }

            $startDate->addDay();
        }

        return $rows;
    }
}
