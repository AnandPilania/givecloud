<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Models\OrderItemFile;

class PurchasedMediaController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member');
        $this->middleware('requires.feature:edownloads');
    }

    public function index()
    {
        pageSetup(__('frontend/accounts.purchased_media.index.my_purchased_media'));

        return $this->renderTemplate('accounts/purchased-media/index');
    }

    public function view($mediaId)
    {
        pageSetup(__('frontend/purchased_media.view.purchased_media_details'), 'content');

        $orderItemFile = OrderItemFile::query()
            ->join('productorderitem', 'productorderitem.id', '=', 'productorderitemfiles.orderitemid')
            ->join('productorder', 'productorder.id', '=', 'productorderitem.productorderid')
            ->whereNull('productorder.deleted_at')
            ->where('productorder.member_id', member('id'))
            ->where('productorderitemfiles.id', $mediaId)
            ->first();

        if (! $orderItemFile) {
            abort(404);
        }

        return $this->renderTemplate('accounts/purchased-media/view', [
            'media' => $orderItemFile,
        ]);
    }
}
