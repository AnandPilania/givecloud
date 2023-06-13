<?php

namespace Ds\Http\Controllers;

use Ds\Models\Membership;
use Ds\Models\Product;
use Ds\Repositories\PageRepository;
use Ds\Repositories\ProductCategoryRepository;

class MembershipController extends Controller
{
    public function destroy()
    {
        user()->canOrRedirect('membership.edit');

        $membership = \Ds\Models\Membership::findWithPermission(request('id'), 'edit');
        $membership->delete();

        $this->flash->success("Successfully deleted '" . $membership->name . "' membership.");

        return redirect()->to('jpanel/memberships');
    }

    public function index()
    {
        user()->canOrRedirect('membership');

        $__menu = 'admin.membership-levels';

        pageSetup(sys_get('syn_groups'), 'jpanel');

        $memberships = Membership::with('promoCodes')
            ->withMemberCount()
            ->get();

        return $this->getView('memberships/index', compact('__menu', 'memberships'));
    }

    public function save()
    {
        user()->canOrRedirect('membership.edit');

        // create
        if (! (is_numeric(request('id')) && request('id') > 0)) {
            $membership = new \Ds\Models\Membership;
        }
        // or update
        else {
            $membership = \Ds\Models\Membership::findWithPermission(request('id'));
        }

        // save data
        $membership->name = request('name');
        $membership->description = request('description');
        $membership->sequence = request('sequence');
        $membership->default_url = request('default_url');
        $membership->renewal_url = request('renewal_url');
        $membership->days_to_expire = request('days_to_expire');
        $membership->should_display_badge = request('should_display_badge') ?? 0;
        $membership->starts_at = (trim(request('starts_at')) !== '') ? \Carbon\Carbon::createFromFormat('M d, Y', request('starts_at')) : null;
        $membership->show_in_profile = request('show_in_profile') == 1;
        $membership->members_can_manage_optin = request('members_can_manage_optin') == 1;
        $membership->members_can_manage_optout = request('members_can_manage_optout') == 1;
        $membership->public_name = request('public_name');
        $membership->public_description = request('public_description');

        // save metadata
        $membership->metadata(request('metadata'));

        $membership->save();

        // remove all existing promocdoes
        $membership->promoCodes()->detach();

        // attach all new promocodes
        if (is_array(request('default_promo_code'))) {
            $membership->promoCodes()->attach(request('default_promo_code'));
        }

        // update dpo data
        if (user()->can('admin.dpo')) {
            $membership->dp_id = request('dp_id');
            $membership->save();
        }

        // purge previous access records
        db_query(sprintf('DELETE FROM membership_access WHERE membership_id = %d', db_real_escape_string($membership->id)));

        // insert new records for category
        $category_insert_values = [];
        if (request('category_ids')) {
            foreach (request('category_ids') as $category_id) {
                $category_insert_values[] = sprintf("(%d, 'product_category', %d)", db_real_escape_string($membership->id), db_real_escape_string($category_id));
            }
            db_query('INSERT INTO membership_access (membership_id, parent_type, parent_id) VALUES ' . implode(', ', $category_insert_values));
        }

        // insert new records for nodes
        $node_insert_values = [];
        if (request('node_ids')) {
            foreach (request('node_ids') as $node_id) {
                $node_insert_values[] = sprintf("(%d, 'node', %d)", db_real_escape_string($membership->id), db_real_escape_string($node_id));
            }
            db_query('INSERT INTO membership_access (membership_id, parent_type, parent_id) VALUES ' . implode(', ', $node_insert_values));
        }

        // insert new records for products
        $product_insert_values = [];
        if (request('product_ids')) {
            foreach (request('product_ids') as $product_id) {
                $product_insert_values[] = sprintf("(%d, 'product', %d)", db_real_escape_string($membership->id), db_real_escape_string($product_id));
            }
            db_query('INSERT INTO membership_access (membership_id, parent_type, parent_id) VALUES ' . implode(', ', $product_insert_values));
        }

        $this->flash->success("'" . request('name') . "' membership saved.");

        return redirect()->to('jpanel/memberships/edit?i=' . $membership->id);
    }

    public function view(PageRepository $pageRepository, ProductCategoryRepository $productCategoryRepository)
    {
        user()->canOrRedirect('membership');

        $__menu = 'admin.membership-levels';

        if (request('i')) {
            $membership = \Ds\Models\Membership::with('promoCodes')->withMemberCount()->findOrFail(request('i'));
            $membership_access = membership_access_get_by_membership($membership->id);
            $title = $membership->name;
        } else {
            $membership = null;
            $membership_access = membership_access_get_by_membership(0);
            $title = 'New ' . sys_get('syn_group');
        }

        pageSetup($title, 'jpanel');

        $promos = \Ds\Models\PromoCode::all();
        $products = Product::select('id', 'name', 'code')->orderBy('name')->orderBy('id')->toBase()->get();

        $pages = $pageRepository->getPageList();
        $categories = $productCategoryRepository->getProductCategoryList();

        return $this->getView('memberships/view', compact(
            '__menu',
            'membership',
            'membership_access',
            'title',
            'promos',
            'products',
            'pages',
            'categories',
        ));
    }
}
