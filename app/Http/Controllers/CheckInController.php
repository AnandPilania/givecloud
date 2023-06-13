<?php

namespace Ds\Http\Controllers;

use Ds\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class CheckInController extends Controller
{
    /**
     * Dash
     *
     * @return \Illuminate\View\View
     */
    public function dash()
    {
        return $this->getView('check_in/dash');
    }

    /**
     * Dash
     *
     * @param string $keywords
     * @return \Illuminate\Support\Collection
     */
    public function search($keywords)
    {
        $keywords = "%$keywords%";

        return OrderItem::with('order', 'variant.product', 'fields', 'checkins')
            ->whereHas('order', function ($q) {
                $q->whereNotNull('confirmationdatetime');
                $q->whereNull('deleted_at');
            })->whereHas('variant', function ($q) {
                return $q->join('product', 'product.id', '=', 'productinventory.productid')
                    ->where('product.allow_check_in', 1);
            })->where(function ($qry) use ($keywords) {
                return $qry->whereHas('fields', function ($qry) use ($keywords) {
                    $qry->where('value', 'like', $keywords);
                })->orWhereHas('order', function ($qry) use ($keywords) {
                    $qry->where('billing_first_name', 'like', $keywords)
                        ->orWhere('billing_last_name', 'like', $keywords)
                        ->orWhere(DB::raw("CONCAT(billing_first_name, ' ', billing_last_name)"), 'like', $keywords)
                        ->orWhere('billingemail', 'like', $keywords)
                        ->orWhere('billingphone', 'like', $keywords);
                });
            })->orderBy('id', 'desc')
            ->take(50)
            ->get()
            ->map(function ($item) {
                $order = $item->order;
                $item = $item->toArray();
                $item['order'] = $order->toArray();

                return $item;
            });
    }
}
