<?php

namespace Ds\Http\Controllers;

use Ds\Models\ShippingTier;

class ShippingTierController extends Controller
{
    public function destroy()
    {
        user()->canOrRedirect('shipping.edit');

        db_query(sprintf('DELETE FROM `shipping_value` WHERE tier_id = %d', db_real_escape_string(request('id'))));
        db_query(sprintf('DELETE FROM `shipping_tier` WHERE id = %d', db_real_escape_string(request('id'))));

        return redirect()->to('jpanel/shipping');
    }

    public function save()
    {
        user()->canOrRedirect('shipping.edit');

        if (request('id')) {
            $shippingTier = ShippingTier::findOrFail(request('id'));
        } else {
            $shippingTier = new ShippingTier;
        }

        $shippingTier->min_value = request('min_value');
        $shippingTier->max_value = request('max_value');
        $shippingTier->save();

        return redirect()->to('jpanel/shipping');
    }

    public function view()
    {
        user()->canOrRedirect('shipping');

        $__menu = 'products.shipping';

        if (request('i')) {
            $title = 'Edit Shipping Tier';
        } else {
            $title = 'Add Shipping Tier';
        }

        pageSetup($title, 'jpanel');

        $qTier = db_query(sprintf(
            'SELECT *
                FROM `shipping_tier` s
                WHERE id = %d',
            request('i')
        ));

        $tier = db_fetch_object($qTier);

        $isNew = db_num_rows($qTier) == 0;

        return $this->getView('shipping_tiers/view', compact('__menu', 'title', 'qTier', 'tier', 'isNew'));
    }
}
