<?php

namespace Ds\Http\Controllers\Frontend;

class OrdersController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member', ['only' => ['index']]);
        $this->middleware('requires.feature:givecloud_pro', ['except' => ['index', 'show']]);
    }

    public function index()
    {
        if (! feature('trackorder')) {
            return response($this->renderTemplate('404'), 404);
        }

        pageSetup(__('frontend/accounts.history.my_history'));

        return $this->renderTemplate('accounts/history', [
            'orders' => member()->orders()->paid()->orderBy('ordered_at', 'desc')->get(),
        ]);
    }

    public function show($number)
    {
        if (! feature('trackorder')) {
            return response($this->renderTemplate('404'), 404);
        }

        $order = \Ds\Models\Order::with('items.variant.product')
            ->where('client_uuid', $number)
            ->where('invoicenumber', $number)
            ->firstOrFail();

        return $this->renderTemplate('receipt', [
            'page_title' => __('frontend/receipt.invoice_number', compact('number')),
            'order' => $order,
        ]);
    }

    public function thankYou($number)
    {
        $order = \Ds\Models\Order::with('items.variant.product')
            ->where('client_uuid', $number)
            ->where('invoicenumber', $number)
            ->firstOrFail();

        return $this->renderTemplate('thank-you', [
            'page_title' => __('frontend/receipt.invoice_number', compact('number')),
            'order' => $order,
        ]);
    }

    /*
     * View a PDF tribute letter.
     *
     * @return \Illuminate\Http\Response
     */
    public function tributePdf($tribute_id)
    {
        // grab tribute
        $tribute = \Ds\Models\Tribute::findOrFail($tribute_id);

        // render PDF]
        return response()->protectedPdf($tribute->getLetterBody());
    }
}
