<?php

namespace Ds\Http\Controllers\Frontend;

class TaxReceiptController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member');
    }

    /**
     * List of tax receipts
     */
    public function index()
    {
        pageSetup(__('frontend/accounts.tax_receipts.index.my_tax_receipts'), 'content');

        $receipts = member()->issuedTaxReceipts()
            ->orderBy('issued_at', 'desc')
            ->get();

        $last_year = (int) \Carbon\Carbon::now()->year;
        $first_year = ($receipts->count() > 0) ? (int) $receipts->last()->ordered_at->year : (int) ($last_year - 1);

        $receipts_by_year = [];
        for ($yr = $last_year; $yr >= $first_year; $yr--) {
            $receipts_by_year[] = [
                'year' => $yr,
                'receipts' => $receipts->filter(function ($r) use ($yr) {
                    return $r->ordered_at->year == $yr;
                })->all(),
            ];
        }

        return $this->renderTemplate('accounts/tax-receipts', [
            'body' => '',
            'receipts_by_year' => collect($receipts_by_year)->toArray(),
        ]);
    }

    /**
     * View record as PDF
     *
     * @param int $receiptId
     */
    public function pdf($receiptId)
    {
        $member = member();

        if (! $member) {
            abort(401);
        }

        $receipt = $member->issuedTaxReceipts()
            ->findOrFail($receiptId);

        return $receipt->toPDF();
    }
}
