<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Shared\DataTable;
use Ds\Domain\Sponsorship\Models\Segment;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Enums\LedgerEntryType;
use Ds\Http\Controllers\Controller;
use Ds\Models\AccountType;
use Ds\Models\Membership;
use Ds\Models\Order;
use Ds\Models\Payment;
use Ds\Models\Product;
use Ds\Models\ProductCategory;
use Ds\Services\Reports\PaymentsDetails\PaymentsDetailsService;
use Illuminate\Http\Response;
use Illuminate\View\View;
use LiveControl\EloquentDataTable\ExpressionWithName;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContributionLineItemsController extends Controller
{
    /** @var \Ds\Services\Reports\PaymentsDetails\PaymentsDetailsService */
    protected $paymentsDetailsService;

    public function __construct(PaymentsDetailsService $paymentsDetailsService)
    {
        parent::__construct();

        $this->paymentsDetailsService = $paymentsDetailsService;
    }

    public function index(): View
    {
        $items = Product::with('variants')->get()->flatMap(function ($product) {
            return $product->variants->map(function ($variant) use ($product) {
                return [
                    'id' => $variant->id,
                    'name' => $product->name . ' - ' . $variant->variantname,
                ];
            });
        })->sortBy('name');

        $billingCountries = collect(Order::getDistinctValuesOf('billingcountry'))->map(function ($country_code) {
            return [
                'code' => $country_code,
                'name' => cart_countries()[$country_code],
            ];
        })->sortBy('name');

        $ipCountries = collect(Order::getDistinctValuesOf('ip_country'))->map(function ($country_code) {
            return [
                'code' => $country_code,
                'name' => cart_countries()[$country_code],
            ];
        })->sortBy('name');

        return view('reports.contribution-line-items.index', [
            'pageTitle' => 'Contribution Line Items',
            'account_types' => AccountType::all(),
            'categories' => ProductCategory::topLevel()->with('childCategories.childCategories.childCategories.childCategories')->orderBy('sequence')->get(),
            'billingCountries' => $billingCountries,
            'ipCountries' => $ipCountries,
            'gateways' => Payment::getDistinctValuesOf('gateway_type'),
            'items' => $items,
            'segments' => Segment::query()->with(['items'])->get(),
            'sponsorships' => Sponsorship::query()->orderBy('first_name')->orderBy('last_name')->get(),
            'memberships' => Membership::all(),
            'donation_forms' => Product::query()->donationForms()->get(),
        ]);
    }

    public function listing(): Response
    {
        $dataTable = new DataTable($this->paymentsDetailsService->filteredQuery(), [
            new ExpressionWithName('ledger_entries.captured_at', 'captured_at'),
            new ExpressionWithName('member.display_name', 'display_name'),
            new ExpressionWithName('IF(ledger_entries.ledgerable_type = "order", CONCAT("Contribution #", productorder.invoicenumber), CONCAT("RPP #", profiles.profile_id))', 'reference'),
            new ExpressionWithName('ledger_entries.type', 'type'),
            new ExpressionWithName('ledger_entries.sponsorship_id', 'sponsorship_id'),
            new ExpressionWithName('ledger_entries.gl_account', 'gl_account'),
            new ExpressionWithName('ledger_entries.qty', 'qty'),
            new ExpressionWithName('ledger_entries.amount', 'amount'),
            'productorder.totalamount',
            new ExpressionWithName('ledger_entries.order_id', 'order_id'),
            new ExpressionWithName('ledger_entries.ledgerable_type', 'ledgerable_type'),
            new ExpressionWithName('ledger_entries.ledgerable_id', 'ledgerable_id'),
            new ExpressionWithName('ledger_entries.supporter_id', 'supporter_id'),
            new ExpressionWithName('ledger_entries.item_id', 'item_id'),
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($row) {
            $payment = optional($row->ledgerable->successfulPayments)->first();
            $currencyCode = $payment->currency ?? $row->order->currency_code;

            return [
                dangerouslyUseHTML(toLocalFormat($row->captured_at, 'M j, Y h:i a')),
                dangerouslyUseHTML(empty($row->supporter_id) ? 'Guest' : sprintf('<a href="%s">%s</a>', route('backend.member.edit', ['id' => $row->supporter_id]), $row->supporter->display_name)),
                view('reports.contribution-line-items._listing.reference', compact('row'))->render(),
                dangerouslyUseHTML(LedgerEntryType::labels()[$row->type]),
                view('reports.contribution-line-items._listing.description', compact('row'))->render(),
                e($row->gl_account),
                e($row->qty ?: 1),
                e(money($row->amount, $currencyCode)->format()),
                e(money($payment->amount ?? 0, $currencyCode)->format()),
                e($currencyCode),
                e($payment->source_type ?? ''),
            ];
        });

        return response($dataTable->make());
    }

    public function export(): StreamedResponse
    {
        set_time_limit(60 * 10); // 10 minutes

        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');

            fputcsv($output, $this->paymentsDetailsService->getHeadersForExport());

            $this->paymentsDetailsService
                ->getRowsForExport()
                ->each(fn ($row) => fputcsv($output, $row));

            fclose($output);
        }, 'contribution-line-items.csv', ['Content-type' => 'text/csv']);
    }
}
