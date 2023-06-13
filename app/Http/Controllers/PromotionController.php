<?php

namespace Ds\Http\Controllers;

use Ds\Http\Requests\PromocodeDuplicateFormRequest;
use Ds\Models\Membership;
use Ds\Models\Product;
use Ds\Models\ProductCategory;
use Ds\Models\PromoCode;
use Illuminate\Support\Str;
use Throwable;

class PromotionController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth');
        $this->middleware('requires.feature:promos');
    }

    public function destroy()
    {
        // check permission
        $promo = PromoCode::findWithPermission(request('id'), 'edit');

        $promo->delete();

        return redirect()->to('jpanel/promotions');
    }

    public function index()
    {
        // check permission
        user()->canOrRedirect('promocode.view');

        $__menu = 'products.promos';

        pageSetup('Promotions', 'jpanel');

        $promos = PromoCode::with('membershipsCount')->orderBy('code')->get();

        return $this->getView('promotions/index', compact('__menu', 'promos'));
    }

    public function duplicate(PromocodeDuplicateFormRequest $request, string $id)
    {
        $originalPromo = PromoCode::findOrFail($id);
        $newCode = $originalPromo->replicate();
        $newCode->code = $request->new_code;
        $newCode->save();

        $newCode->categories()->sync($originalPromo->categories->pluck('id'));
        $newCode->products()->sync($originalPromo->products->pluck('id'));
        $newCode->memberships()->sync($originalPromo->memberships->pluck('id'));

        return redirect()->to(route('backend.promotions.edit', [$newCode]));
    }

    public function save()
    {
        // create record if it doesn't exist
        if (! request()->filled('id')) {
            // check permission
            user()->canOrRedirect('promocode.add');

            // create promo
            $promo_by_id = new PromoCode;
            $promo_by_id->code = request('code');
            $promo_by_id->save();

            // promo
            $promo = PromoCode::find($promo_by_id->id);
        } else {
            // check permission
            user()->canOrRedirect('promocode.edit');

            // existing promo
            $promo = PromoCode::find(request('id'));
        }

        // save promo data
        $promo->code = request('code');
        $promo->description = request('description');
        $promo->discount_type = request('discount_type');
        $promo->discount = request('discount');
        $promo->startdate = (request('startdate')) ? toUtc(request('startdate')) : null;
        $promo->enddate = (request('enddate')) ? toUtc(request('enddate')) : null;
        $promo->is_free_shipping = (request('is_free_shipping') == 1);
        $promo->free_shipping_label = request('free_shipping_label');
        $promo->allocation_limit = Str::startsWith(request('discount_type'), 'bxgy_') ? request('allocation_limit') : null;
        $promo->buy_quantity = Str::startsWith(request('discount_type'), 'bxgy_') ? request('buy_quantity', 1) : null;
        $promo->usage_limit = (trim(request('usage_limit'))) ? request('usage_limit') : null;
        $promo->usage_limit_per_account = (trim(request('usage_limit_per_account'))) ? request('usage_limit_per_account') : null;
        $promo->save();

        // update category linkages
        // clear existing links
        db_query(sprintf('DELETE FROM productpromocodecategory WHERE promocodeid = %d', db_real_escape_string($promo->id)));
        // insert new links
        if (request('categoryids')) {
            foreach (request('categoryids') as $i => $v) {
                db_query(sprintf(
                    'INSERT INTO productpromocodecategory (promocodeid,categoryid)
                        VALUES (%d,%d)',
                    db_real_escape_string($promo->id),
                    db_real_escape_string($v)
                ));
            }
        }

        // update product linkages
        // clear existing links
        db_query(sprintf('DELETE FROM productpromocodeproduct WHERE promocodeid = %d', db_real_escape_string($promo->id)));
        // insert new links
        if (request('productids')) {
            foreach (request('productids') as $i => $v) {
                db_query(sprintf(
                    'INSERT INTO productpromocodeproduct (promocodeid,productid)
                        VALUES (%d,%d)',
                    db_real_escape_string($promo->id),
                    db_real_escape_string($v)
                ));
            }
        }

        // link up membership_ids
        $promo->memberships()->sync(request('membership_ids', []));

        return redirect()->to('jpanel/promotions');
    }

    public function view($id = null)
    {
        $__menu = 'products.promos';

        if (request('i')) {
            $id = request('i');
        }

        if ($id) {
            user()->can('promocode.edit');
            $promocode = PromoCode::findOrFail($id);
            $title = $promocode->code;
        } else {
            user()->can('promocode.add');
            $promocode = new PromoCode;
            $promocode->discount_type = 'percent';
            $title = 'Add Promotion';
        }
        $isNew = ! $promocode->exists;

        $allMemberships = Membership::all();
        $allCategories = ProductCategory::all();

        $allProducts = Product::all();
        $selectedProductIds = $promocode->products->pluck('id')->toArray();

        pageSetup($title, 'jpanel');

        return view('promotions.view', compact('__menu', 'promocode', 'title', 'isNew', 'allMemberships', 'allCategories', 'allProducts', 'selectedProductIds'));
    }

    /**
     * Autocomplete a selectize input.
     * GET method
     * Expects 'query' input (search terms)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function autocomplete()
    {
        $keywords = strtolower(trim(request()->query('query')));

        if ($keywords === '') {
            return response()->json([]);
        }

        // get members
        $matches = PromoCode::notExpired()
            ->select('code')
            ->whereRaw('lower(code) LIKE ?', ['%' . $keywords . '%'])
            ->orderBy('code')
            ->get();

        // return json
        return response()->json($matches);
    }

    /**
     * Used to recaclulate the usage count for a
     * given promocode.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function calculate_usage($id)
    {
        $promo = PromoCode::findOrFail($id);

        try {
            $promo->calculateUsageCount();
            $this->flash->success('Usage count recalculated.');
        } catch (Throwable $e) {
            notifyException($e);
            $this->flash->error($e->getMessage());
        }

        return redirect()->route('backend.promotions.edit', [$promo]);
    }
}
