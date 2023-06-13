<?php

namespace Ds\Mail;

use Carbon\Carbon;
use Closure;
use Ds\Domain\Analytics\Models\AnalyticsEvent;
use Ds\Domain\Shared\Date;
use Ds\Domain\Shared\DateTime;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Http\Resources\DonationForms\DonationFormResource;
use Ds\Models\Media;
use Ds\Models\Member;
use Ds\Models\Product;
use Ds\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContributionsDailyDigest extends Mailable
{
    use SerializesModels;
    use Queueable;

    /** @var \Ds\Domain\Shared\Date */
    protected $date;

    /** @var \Ds\Models\User */
    protected $user;

    public function __construct(Date $date, User $user)
    {
        $this->date = $date;
        $this->user = $user;
    }

    public function build()
    {
        Carbon::setTestNow($this->date);

        $data = [
            'date' => fromLocal($this->date)->subDay(),
            'date_in_previous_month' => fromLocal($this->date)->subDay()->subMonthWithoutOverflow(),
            'user' => $this->user,
            'greeting' => $this->getGreeting(),
            'random_emoji' => fn () => $this->getRandomEmoji(),
            'currency_code' => (string) currency(),
            'month_previous_year' => $this->date->clone()->subYearWithoutOverflow(),
            'combine_revenue' => $this->getCombineRevenue(),
            'combine_supporters' => $this->getCombineSupporters(),
            'new_revenue' => $this->getNewRevenue(),
            'new_supporters' => $this->getNewSupporters(),
            'recurring_revenue' => $this->getRecurringRevenue(),
            'recurring_supporters' => $this->getRecurringSupporters(),
            'repeat_revenue' => $this->getRepeatRevenue(),
            'repeat_supporters' => $this->getRepeatSupporters(),
            'yesterdays_abandoned_carts' => $this->getYesterdaysAbandonedCarts(),
            'yesterdays_failed_recurring_payments' => $this->getYesterdaysFailedRecurringPayments(),
            'yesterdays_suspended_recurring_profiles' => $this->getYesterdaysSuspendedRpps(),
            'yesterdays_cancelled_recurring_profiles' => $this->getYesterdaysCancelledRpps(),
            'month_revenue' => $this->getMonthRevenue(),
            'month_supporters' => $this->getMonthSupporters(),
            'notable_activity_best_revenue' => $this->getNotableActivityBestRevenue(),
            'notable_activity_best_engagement' => $this->getNotableActivityBestEngagement(),
            'notable_activity_highest_conversion_rate' => $this->getNotableActivityHighestConversionRate(),
            'notable_activity_best_p2p_fundraiser' => $this->getNotableActivityBestP2PFundraiser(),
            'notable_activity_largest_contribution' => $this->getNotableActivityLargestContribution(),
            'keep_your_eyes_on' => [],
        ];

        $data['combine_revenue_change'] = $this->getCombineRevenueChange($data['combine_revenue']);
        $data['combine_supporters_change'] = $this->getCombineSupportersChange($data['combine_supporters']);
        $data['month_revenue_change'] = $this->getMonthRevenueChange($data['month_revenue']);
        $data['month_supporters_change'] = $this->getMonthSupportersChange($data['month_supporters']);

        if ($data['yesterdays_abandoned_carts']) {
            $data['keep_your_eyes_on'][] = (object) [
                'content' => sprintf('%s Abandoned Carts', $data['yesterdays_abandoned_carts']),
                'permalink' => route('backend.orders.abandoned_carts', ['fd1' => $data['date']->toDateFormat(), 'fd2' => $data['date']->toDateFormat()]),
                'icon_image_url' => 'https://cdn.givecloud.co/s/assets/icons/abandoned-carts.png',
                'icon_background_colour' => '#fdf5e5',
                'icon_colour' => '#f2a649',
            ];
        }

        if ($data['yesterdays_failed_recurring_payments']) {
            $data['keep_your_eyes_on'][] = (object) [
                'content' => sprintf('%s Failed Recurring Payments', $data['yesterdays_failed_recurring_payments']),
                'permalink' => route('backend.reports.transactions.index', ['payment_status' => 'fail', 'ordertime_str' => $data['date']->toDateFormat(), 'ordertime_end' => $data['date']->toDateFormat()]),
                'icon_image_url' => 'https://cdn.givecloud.co/s/assets/icons/payments-failed.png',
                'icon_background_colour' => '#fceeee',
                'icon_colour' => '#ea5554',
            ];
        }

        if ($data['yesterdays_suspended_recurring_profiles']) {
            $data['keep_your_eyes_on'][] = (object) [
                'content' => sprintf('%s Suspended Recurring Payments', $data['yesterdays_suspended_recurring_profiles']),
                'permalink' => route('backend.recurring_payments.index', ['status' => 'Suspended', 'nextbilldate_str' => $data['date']->toDateFormat(), 'nextbilldate_end' => $data['date']->toDateFormat()]),
                'icon_image_url' => 'https://cdn.givecloud.co/s/assets/icons/payments-suspended.png',
                'icon_background_colour' => '#fdf5e5',
                'icon_colour' => '#f2a649',
            ];
        }

        if ($data['yesterdays_cancelled_recurring_profiles']) {
            $data['keep_your_eyes_on'][] = (object) [
                'content' => sprintf('%s Cancelled Recurring Payments', $data['yesterdays_cancelled_recurring_profiles']),
                'permalink' => route('backend.recurring_payments.index', ['status' => 'Cancelled', 'enddate_str' => $data['date']->toDateFormat(), 'enddate_end' => $data['date']->toDateFormat()]),
                'icon_image_url' => 'https://cdn.givecloud.co/s/assets/icons/payments-cancelled.png',
                'icon_background_colour' => '#fceeee',
                'icon_colour' => '#ea5554',
            ];
        }

        Carbon::setTestNow();

        return $this
            ->view('mailables.contributions-daily-digest', $data)
            ->subject(sprintf(
                "Yesterday's Fundraising: %s %s",
                money($data['new_revenue'])->format('$0,0[.]00 $$$'),
                $data['random_emoji'](),
            ));
    }

    private function getGreeting(): string
    {
        $greeting = Arr::random([
            'A joyous {weekday}',
            'Beautiful {weekday}',
            'Buenos dias',
            'Good morning',
            'Happy {weekday}',
            'Hola',
            'Howdy-do',
            'Top of the morning',
        ]);

        return Str::replace(
            '{weekday}',
            ucwords(fromLocalFormat($this->date, 'l')),
            $greeting,
        );
    }

    private function getRandomEmoji(): string
    {
        return Str::of('❤👌☀🎉🔥💪👍😍💕🙃✨🚀')
            ->split('//u', -1, PREG_SPLIT_NO_EMPTY)
            ->random();
    }

    private function applyWhereBetween(string $column, $startDate, $endDate = null): Closure
    {
        return function ($query) use ($column, $startDate, $endDate) {
            $query->whereBetween($column, [
                fromLocal($startDate)->startOfDay()->toUtc(),
                fromLocal($endDate ?? $startDate)->endOfDay()->toUtc(),
            ]);
        };
    }

    private function getChangePercentage(float $currentValue, float $previousValue): ?int
    {
        if (empty($previousValue)) {
            return null;
        }

        return  ($currentValue - $previousValue) / $previousValue * 100;
    }

    private function getProductThumbnail(Product $product): ?string
    {
        if ($product->is_fundraising_form) {
            $donationForm = DonationFormResource::make($product)->toObject();

            $thumbnail = media_thumbnail(
                $product->metadata['donation_forms_social_preview_image'] ?? $product->metadata['donation_forms_background_image'] ?? $donationForm->preview_image_url,
                ['50x50', 'crop' => 'entropy'],
            );
        }

        return $thumbnail ?? media_thumbnail($product) ?: image_thumbnail(null);
    }

    private function getRevenueForProduct(Product $product): float
    {
        return DB::table('productorderitem as i')
            ->join('productorder as c', 'c.id', 'i.productorderid')
            ->join('productinventory as v', 'v.id', 'i.productinventoryid')
            ->where($this->applyWhereBetween('c.confirmationdatetime', 'yesterday'))
            ->where('v.productid', $product->id)
            ->whereNull('c.refunded_at')
            ->sum(DB::raw('(i.qty * i.price + if(i.dcc_eligible, i.dcc_amount, 0)) * c.functional_exchange_rate'));
    }

    private function getContributionFunctionalTotalMinusRefuned(): Expression
    {
        return DB::raw('(c.totalamount - ifnull(c.refunded_amt, 0)) * c.functional_exchange_rate');
    }

    private function getPaymentFunctionalTotalMinusRefuned(): Expression
    {
        return DB::raw('(p.amount - p.amount_refunded) * p.functional_exchange_rate');
    }

    private function getTransactionFunctionalTotalMinusRefuned(): Expression
    {
        return DB::raw('(t.amt - ifnull(t.refunded_amt, 0)) * t.functional_exchange_rate');
    }

    private function getCombineRevenue(DateTime $date = null): float
    {
        return DB::table('payments as p')
            ->where($this->applyWhereBetween('p.captured_at', $date ?? 'yesterday'))
            ->sum($this->getPaymentFunctionalTotalMinusRefuned());
    }

    private function getCombineRevenueChange(float $revenue): ?int
    {
        $revenueLastMonth = $this->getCombineRevenue(fromLocal('yesterday')->subMonthWithoutOverflow());

        return $this->getChangePercentage($revenue, $revenueLastMonth);
    }

    private function getNewRevenue(DateTime $date = null): float
    {
        return DB::table('productorder as c')
            ->leftJoin('member as s', 's.id', 'c.member_id')
            ->where($this->applyWhereBetween('c.confirmationdatetime', $date ?? 'yesterday'))
            ->where(function (Builder $query) {
                $query->whereNull('s.first_payment_at');
                $query->orWhereRaw("date(convert_tz(c.confirmationdatetime, 'UTC', '" . localOffset() . "')) = date(convert_tz(s.first_payment_at, 'UTC', '" . localOffset() . "'))");
            })->sum($this->getContributionFunctionalTotalMinusRefuned());
    }

    private function getRecurringRevenue(DateTime $date = null): float
    {
        return DB::table('transactions as t')
            ->where($this->applyWhereBetween('t.order_time', $date ?? 'yesterday'))
            ->where('t.payment_status', 'Completed')
            ->sum($this->getTransactionFunctionalTotalMinusRefuned());
    }

    private function getRepeatRevenue(DateTime $date = null): float
    {
        return DB::table('productorder as c')
            ->join('member as s', 's.id', 'c.member_id')
            ->where($this->applyWhereBetween('c.confirmationdatetime', $date ?? 'yesterday'))
            ->whereNotNull('s.first_payment_at')
            ->whereDate('c.confirmationdatetime', '>', DB::raw('date(s.first_payment_at)'))
            ->sum($this->getContributionFunctionalTotalMinusRefuned());
    }

    private function getCombineSupporters(DateTime $date = null): float
    {
        return DB::table('payments as p')
            ->join('member as s', 's.id', 'p.source_account_id')
            ->where($this->applyWhereBetween('p.captured_at', $date ?? 'yesterday'))
            ->distinct('s.id')
            ->count();
    }

    private function getCombineSupportersChange(int $supporters): ?int
    {
        $supportersLastMonth = $this->getCombineSupporters(fromLocal('yesterday')->subMonthWithoutOverflow());

        return $this->getChangePercentage($supporters, $supportersLastMonth);
    }

    private function getNewSupporters(DateTime $date = null): int
    {
        return DB::table('productorder as c')
            ->join('member as s', 's.id', 'c.member_id')
            ->where($this->applyWhereBetween('c.confirmationdatetime', $date ?? 'yesterday'))
            ->where('s.last_payment_at', DB::raw('s.first_payment_at'))
            ->distinct('s.id')
            ->count();
    }

    private function getRecurringSupporters(DateTime $date = null): int
    {
        return DB::table('transactions as t')
            ->join('recurring_payment_profiles as r', 'r.id', 't.recurring_payment_profile_id')
            ->where($this->applyWhereBetween('t.order_time', $date ?? 'yesterday'))
            ->where('t.payment_status', 'Completed')
            ->distinct('r.member_id')
            ->count();
    }

    private function getRepeatSupporters(DateTime $date = null): int
    {
        return DB::table('productorder as c')
            ->join('member as s', 's.id', 'c.member_id')
            ->where($this->applyWhereBetween('c.confirmationdatetime', $date ?? 'yesterday'))
            ->where('s.last_payment_at', '>', DB::raw('s.first_payment_at'))
            ->distinct('s.id')
            ->count();
    }

    private function getYesterdaysAbandonedCarts(): int
    {
        return DB::table('productorder as c')
            ->where($this->applyWhereBetween('c.createddatetime', 'yesterday'))
            ->whereNull('c.confirmationdatetime')
            ->where('c.is_pos', false)
            ->where('c.total_qty', '>', 0)
            ->count();
    }

    private function getYesterdaysFailedRecurringPayments(): int
    {
        return DB::table('transactions as t')
            ->where($this->applyWhereBetween('t.order_time', 'yesterday'))
            ->where('t.payment_status', '!=', 'Completed')
            ->count();
    }

    private function getYesterdaysSuspendedRpps(): int
    {
        return DB::table('recurring_payment_profiles as r')
            ->where($this->applyWhereBetween('r.next_billing_date', fromDate('yesterday')))
            ->where('r.status', RecurringPaymentProfileStatus::SUSPENDED)
            ->count();
    }

    private function getYesterdaysCancelledRpps(): int
    {
        return DB::table('recurring_payment_profiles as r')
            ->where($this->applyWhereBetween('r.final_payment_due_date', 'yesterday'))
            ->where('r.status', 'Cancelled')
            ->count();
    }

    private function getMonthRevenue(DateTime $startDate = null, DateTime $endDate = null): float
    {
        return DB::table('payments as p')
            ->where($this->applyWhereBetween(
                'p.captured_at',
                $startDate ?? fromLocal('yesterday')->startOfMonth(),
                $endDate ?? 'yesterday',
            ))->sum($this->getPaymentFunctionalTotalMinusRefuned());
    }

    private function getMonthRevenueChange(float $revenue): ?int
    {
        $revenueThisMonthLastYear = $this->getMonthRevenue(
            fromLocal('yesterday')->subYearWithoutOverflow()->startOfMonth(),
            fromLocal('yesterday')->subYearWithoutOverflow(),
        );

        return $this->getChangePercentage($revenue, $revenueThisMonthLastYear);
    }

    private function getMonthSupporters(DateTime $startDate = null, DateTime $endDate = null): int
    {
        return DB::table('payments as p')
            ->where($this->applyWhereBetween(
                'p.captured_at',
                $startDate ?? fromLocal('yesterday')->startOfMonth(),
                $endDate ?? 'yesterday',
            ))->distinct('source_account_id')
            ->count();
    }

    private function getMonthSupportersChange(int $supporters): ?int
    {
        $supportersThisMonthLastYear = $this->getMonthSupporters(
            fromLocal('yesterday')->subYearWithoutOverflow()->startOfMonth(),
            fromLocal('yesterday')->subYearWithoutOverflow(),
        );

        return $this->getChangePercentage($supporters, $supportersThisMonthLastYear);
    }

    private function getNotableActivityBestRevenue(): ?object
    {
        $contributions = DB::table('productorderitem as i')
            ->join('productorder as c', 'c.id', 'i.productorderid')
            ->join('productinventory as v', 'v.id', 'i.productinventoryid')
            ->join('product as p', 'p.id', 'v.productid')
            ->select([
                'p.id as product_id',
                DB::raw('count(distinct c.id) as contribution_count'),
                DB::raw('sum((i.qty * i.price + if(i.dcc_eligible, i.dcc_amount, 0)) * c.functional_exchange_rate) as revenue'),
            ])->where($this->applyWhereBetween('c.confirmationdatetime', 'yesterday'))
            ->whereNull('c.refunded_at')
            ->groupBy('p.id')
            ->orderByDesc('revenue')
            ->first();

        if (empty($contributions)) {
            return null;
        }

        $product = Product::find($contributions->product_id);

        return (object) [
            'title' => $product->name,
            'value' => $contributions->contribution_count,
            'revenue' => $contributions->revenue,
            'thumbnail' => $this->getProductThumbnail($product),
            'permalink' => $product->admin_url,
        ];
    }

    private function getNotableActivityBestEngagement(): ?object
    {
        $engagement = DB::query()
            ->select([
                'product_id',
                'product_name',
                DB::raw('sum(if(engaged_views > 0, 1, 0)) as total_engaged_views'),
                DB::raw('sum(if(engaged_views > 0, 1, 0)) / sum(if(views > 0, 1, 0)) * 100 as engagement'),
            ])->fromSub(
                DB::table('analytics_events as e')
                    ->select([
                        'p.id as product_id',
                        'p.name as product_name',
                        DB::raw("sum(if((e.event_category = 'fundraising_forms.modal_embed' and e.event_name = 'open') or (e.event_category = 'fundraising_forms.hosted_page' and e.event_name = 'pageview') or (e.event_category = 'fundraising_forms.inline_embed' and e.event_name = 'impression'), 1, 0)) as views"),
                        DB::raw("sum(if(e.event_category in ('fundraising_forms.hosted_page', 'fundraising_forms.inline_embed', 'fundraising_forms.modal_embed') and e.event_name not in ('impression', 'open', 'pageview'), 1, 0)) as engaged_views"),
                    ])->join('product as p', function (JoinClause $join) {
                        $join->on('e.eventable_id', 'p.id');
                        $join->where('e.eventable_type', (new Product)->getMorphClass());
                    })->where($this->applyWhereBetween('e.created_at', 'yesterday'))
                    ->whereIn('e.event_category', ['fundraising_forms.hosted_page', 'fundraising_forms.inline_embed', 'fundraising_forms.modal_embed'])
                    ->groupBy('e.eventable_id', 'e.analytics_visit_id'),
                'agg',
            )->groupBy('product_id')
            ->having('engagement', '>', 0)
            ->having('total_engaged_views', '>=', 5)
            ->orderByDesc('engagement')
            ->orderByDesc('total_engaged_views')
            ->first();

        if (empty($engagement)) {
            return null;
        }

        $product = Product::find($engagement->product_id);
        $revenue = $this->getRevenueForProduct($product);

        return (object) [
            'title' => $engagement->product_name,
            'value' => $engagement->engagement,
            'revenue' => $revenue,
            'thumbnail' => $this->getProductThumbnail($product),
            'permalink' => $product->admin_url,
        ];
    }

    private function getNotableActivityHighestConversionRate(): ?object
    {
        $conversions = DB::table('productorderitem as i')
            ->join('productorder as c', 'c.id', 'i.productorderid')
            ->join('productinventory as v', 'v.id', 'i.productinventoryid')
            ->join('product as p', 'p.id', 'v.productid')
            ->select([
                'p.id as product_id',
                'p.name as product_name',
                DB::raw('count(c.id) as contributions'),
            ])->where($this->applyWhereBetween('c.createddatetime', 'yesterday'))
            ->whereNull('c.refunded_at')
            ->whereNotNull('c.confirmationdatetime')
            ->groupBy('p.id')
            ->orderByDesc('contributions')
            ->first();

        if (empty($conversions)) {
            return null;
        }

        $views = AnalyticsEvent::from('analytics_events as e')
            ->opensImpressionsOrPageviews('e')
            ->where($this->applyWhereBetween('e.created_at', 'yesterday'))
            ->where('e.eventable_id', $conversions->product_id)
            ->where('e.eventable_type', (new Product)->getMorphClass())
            ->count();

        $product = Product::find($conversions->product_id);
        $revenue = $this->getRevenueForProduct($product);

        return (object) [
            'title' => $conversions->product_name,
            'value' => $conversions->contributions / max($conversions->contributions, $views) * 100,
            'revenue' => $revenue,
            'thumbnail' => $this->getProductThumbnail($product),
            'permalink' => $product->admin_url,
        ];
    }

    private function getNotableActivityBestP2PFundraiser(): ?object
    {
        $fundraiser = DB::table('productorderitem as i')
            ->join('productorder as c', 'c.id', 'i.productorderid')
            ->join('fundraising_pages as fp', 'fp.id', 'i.fundraising_page_id')
            ->select([
                'fp.title',
                'fp.photo_id as media_id',
                'fp.id as fundraiser_id',
                DB::raw('count(c.id) as contributions'),
                DB::raw('sum((i.qty * i.price + if(i.dcc_eligible, i.dcc_amount, 0)) * c.functional_exchange_rate) as revenue'),
            ])->where($this->applyWhereBetween('c.confirmationdatetime', 'yesterday'))
            ->whereNull('c.refunded_at')
            ->groupBy('fp.id')
            ->orderByDesc('revenue')
            ->first();

        if (empty($fundraiser)) {
            return null;
        }

        return (object) [
            'title' => strip_tags($fundraiser->title),
            'value' => $fundraiser->contributions,
            'revenue' => $fundraiser->revenue,
            'thumbnail' => Media::find($fundraiser->media_id)->thumbnail_url ?? null,
            'permalink' => route('backend.fundraising-pages.view', [$fundraiser->fundraiser_id]),
        ];
    }

    private function getNotableActivityLargestContribution(): ?object
    {
        $contribution = DB::table('productorderitem as i')
            ->join('productorder as c', 'c.id', 'i.productorderid')
            ->select([
                'c.is_anonymous',
                'c.id as contribution_id',
                'c.member_id as supporter_id',
                'c.billing_first_name as supporter_first_name',
                'c.billing_last_name as supporter_last_name',
                'c.billingemail as supporter_email',
                DB::raw('((i.qty * i.price + if(i.dcc_eligible, i.dcc_amount, 0)) * c.functional_exchange_rate) as revenue'),
            ])->where($this->applyWhereBetween('c.confirmationdatetime', 'yesterday'))
            ->whereNull('c.refunded_at')
            ->having('revenue', '>', 0)
            ->orderByDesc('revenue')
            ->first();

        if (empty($contribution)) {
            return null;
        }

        if ($contribution->supporter_id) {
            $supporter = Member::find($contribution->supporter_id);
        }

        return (object) [
            'title' => trim("{$contribution->supporter_first_name} {$contribution->supporter_last_name}"),
            'value' => null,
            'revenue' => $contribution->revenue,
            'thumbnail' => $supporter->avatar ?? gravatar($contribution->supporter_email, 'mp'),
            'permalink' => route('backend.orders.edit', [$contribution->contribution_id]),
        ];
    }
}
