<?php

namespace Ds\Http\Controllers;

use Ds\Models\ShippingMethod;

class ShippingController extends Controller
{
    public function destroy()
    {
        user()->canOrRedirect('shipping.edit');

        db_query('DELETE FROM `shipping_value` WHERE method_id = %d', db_real_escape_string(request('id')));

        $shippingMethod = ShippingMethod::findOrFail(request('id'));
        $shippingMethod->deleted_at = now();
        $shippingMethod->deleted_by = user('id');
        $shippingMethod->save();

        $this->flash->success($shippingMethod->name . ' deleted successfully!');

        return redirect()->to('jpanel/shipping');
    }

    public function index()
    {
        user()->canOrRedirect('shipping');

        $__menu = 'products.shipping';

        pageSetup('Flat Rate Shipping', 'jpanel');

        $methods = \Ds\Models\ShippingMethod::all();
        $tiers = \Ds\Models\ShippingTier::orderBy('min_value')->get();

        return $this->getView('shipping/index', compact('__menu', 'methods', 'tiers'));
    }

    public function save()
    {
        user()->canOrRedirect('shipping.edit');

        // create record if it doesn't exist
        if (request()->filled('id')) {
            $method = ShippingMethod::find(request('id'));
        } else {
            $method = new ShippingMethod;
        }

        // change is_default on other methods, if necessary
        if ($method->is_default != request('is_method')) {
            ShippingMethod::query()->update([
                'is_default' => 0,
            ]);
        }

        // update record
        $method->name = request('name');
        $method->code = request('code');
        $method->description = request('description');
        $method->is_default = (request('is_default') == 1);
        $method->countries = (is_array(request('countries'))) ? request('countries') : null;
        $method->regions = (is_array(request('regions'))) ? request('regions') : null;
        $method->priority = request('priority');
        $method->save();

        // update tiered pricing
        $qDeleteValues = db_query(sprintf(
            'DELETE FROM shipping_value
                WHERE method_id = %d',
            db_real_escape_string($method->id)
        ));

        // insert new tiered pricing
        if (is_array(request('tier'))) {
            foreach (request('tier') as $tier_id => $amount) {
                $qNewValues = db_query(sprintf(
                    'INSERT INTO shipping_value (method_id, tier_id, amount)
                        VALUES (%d, %d, %f)',
                    db_real_escape_string($method->getKey()),
                    db_real_escape_string($tier_id),
                    db_real_escape_string($amount)
                ));
            }
        }

        $this->flash->success($method->name . ' saved successfully!');

        return redirect()->to('jpanel/shipping');
    }

    public function view()
    {
        user()->canOrRedirect('shipping');

        $__menu = 'products.shipping';

        if (request('i')) {
            $method = ShippingMethod::findOrFail(request('i'));
            $title = $method->name;
        } else {
            $method = new ShippingMethod;
            $title = 'Add Shipping Method';
        }

        $tiers = \Ds\Models\ShippingTier::all();

        pageSetup($title, 'jpanel');

        return $this->getView('shipping/view', compact('__menu', 'method', 'title', 'tiers'));
    }
}
