<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Flatfile\Services\Contributions as FlatfileContributionsService;
use Ds\Domain\Shared\DataTable;
use Ds\Models\Contribution;
use Ds\Models\Product;
use Ds\Services\Reports\Contributions\ContributionReportService;
use Illuminate\Http\Response;
use Illuminate\View\View;
use LiveControl\EloquentDataTable\ExpressionWithName;

class ContributionController extends Controller
{
    public function index(): View
    {
        user()->canOrRedirect('order');

        if (Contribution::doesntExist()) {
            return view('contributions.empty');
        }

        return view('contributions.index', [
            'unsynced_count' => Contribution::query()->unsynced()->count(),
            'donation_forms' => Product::query()->donationForms()->get(),
            'flatfileToken' => app(FlatfileContributionsService::class)->token(),
        ]);
    }

    public function listing(): Response
    {
        user()->canOrRedirect('order');

        $contributions = app(ContributionReportService::class)->filteredQuery();

        $dataTable = new DataTable($contributions, [
            new ExpressionWithName('contributions.id', 'id'), // Checkbox
            new ExpressionWithName('contributions.id', 'col2'), // Avatar
            new ExpressionWithName("COALESCE(member.display_name, 'Anonymous Donor')", 'display_name'), // Supporter
            'is_recurring', // Recurring
            'total', // Payment
            new ExpressionWithName('contributions.id', 'net'), // Net
            'contribution_date',
            new ExpressionWithName('contributions.id', 'col3'), // Status

            // off the grid
            new ExpressionWithName('member.email', 'email'),
            new ExpressionWithName('contributions.payment_type', 'payment_type'),
            new ExpressionWithName('contributions.currency_code', 'currency_code'),
            new ExpressionWithName('contributions.total_refunded', 'total_refunded'),
            new ExpressionWithName('contributions.is_dpo_synced', 'is_dpo_synced'),
            new ExpressionWithName('contributions.payment_card_brand', 'payment_card_brand'),
            new ExpressionWithName('contributions.payment_card_last4', 'payment_card_last4'),
            new ExpressionWithName('contributions.is_spam', 'is_spam'),
            new ExpressionWithName('contributions.is_test', 'is_test'),
            new ExpressionWithName('contributions.billing_country', 'billing_country'),
            new ExpressionWithName('contributions.is_fulfilled', 'is_fulfilled'),
            new ExpressionWithName('contributions.shippable_items', 'shippable_items'),
            new ExpressionWithName('contributions.supporter_id', 'supporter_id'),
            new ExpressionWithName('contributions.payment_id', 'payment_id'),
            new ExpressionWithName('contributions.payment_status', 'payment_status'),
            new ExpressionWithName('contributions.functional_exchange_rate', 'functional_exchange_rate'),
            new ExpressionWithName('contributions.functional_currency_code', 'functional_currency_code'),
        ]);

        $checkboxView = view('contributions._listing.checkbox');
        $thumbsView = view('contributions._listing.thumbs');
        $supporterView = view('contributions._listing.supporter');
        $recurringView = view('contributions._listing.recurring');
        $statusView = view('contributions._listing.status');
        $paymentView = view('contributions._listing.payment');
        $netView = view('contributions._listing.net-amount');
        $dateView = view('contributions._listing.date');

        $dataTable->setFormatRowFunction(function ($contribution) use (
            $checkboxView,
            $thumbsView,
            $supporterView,
            $recurringView,
            $statusView,
            $paymentView,
            $netView,
            $dateView
        ) {
            $route = '#';

            if ($contribution->order) {
                $route = route('backend.orders.edit', $contribution->order->id);
            }

            if ($contribution->transactions->isNotEmpty()) {
                $route = route('backend.recurring_payments.show', $contribution->transactions[0]->recurringPaymentProfile->profile_id);
            }

            return [
                $checkboxView->with(compact('contribution'))->render(),
                $thumbsView->with(compact('contribution'))->render(),
                $supporterView->with(compact('contribution'))->render(),
                $recurringView->with(compact('contribution'))->render(),
                $paymentView->with(compact('contribution'))->render(),
                $netView->with(compact('contribution'))->render(),
                $dateView->with(compact('contribution'))->render(),
                $statusView->with(compact('contribution'))->render(),
                dangerouslyUseHTML('<a href="' . $route . '">View</a>'),
                e($route),
            ];
        });

        return response($dataTable->withManualCount()->make());
    }
}
