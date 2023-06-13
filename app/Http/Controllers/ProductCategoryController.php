<?php

namespace Ds\Http\Controllers;

class ProductCategoryController extends Controller
{
    public function destroy()
    {
        // make sure they can edit
        \Ds\Models\ProductCategory::findOrFail(request('id'))->userCanOrRedirect('edit');

        $qDeleteLinks = db_query(sprintf("DELETE FROM `membership_access` WHERE parent_type = 'product_category' AND parent_id = %d", db_real_escape_string(request('id'))));
        $qDeleteLinks = db_query(sprintf("DELETE FROM `node` WHERE type = 'category' AND category_id = %d", db_real_escape_string(request('id'))));
        $qDeleteLinks = db_query(sprintf('DELETE FROM `productcategory` WHERE parent_id = %d', db_real_escape_string(request('id'))));
        $qDelete = db_query(sprintf('DELETE FROM `productcategory` WHERE id = %d', db_real_escape_string(request('id'))));

        $this->flash->success('Category deleted!');

        return redirect()->to('jpanel/products/categories');
    }

    public function index()
    {
        user()->canOrRedirect('productcategory.view');

        $__menu = 'products.categories';

        pageSetup('Categories', 'jpanel');

        return $this->getView('product_categories/index', compact('__menu'));
    }

    public function save()
    {
        // if the category doesn't exist, creat it
        if (! (is_numeric(request('id')) && request('id') > 0)) {
            $category = \Ds\Models\ProductCategory::newWithPermission();

        // find existing category
        } else {
            $category = \Ds\Models\ProductCategory::findWithPermission(request('id'), 'edit');
        }

        // save category details
        $category->name = request()->input('name');
        $category->description = request()->input('description');
        $category->sequence = request()->input('sequence');
        $category->parent_id = (int) (request()->input('parent_id')) ?: null;
        $category->url_name = request()->input('url_name');
        $category->template_suffix = request()->input('template_suffix');
        $category->media_id = request()->input('media_id');

        if ($metadata = request('metadata')) {
            $category->metadata($metadata);
        }

        $category->save();

        // category image (DEPRECATED)
        if ($media = \Ds\Models\Media::storeUpload('imageserverfile')) {
            $category->media_id = $media->id;
            $category->save();
        }

        // update menu
        if (request('_update_menu')) {
            \Ds\Models\ProductCategory::find($category->id)->updateNodes();
        }

        $this->flash->success('Category saved!');

        return redirect()->to('/jpanel/products/categories/edit?i=' . $category->id);
    }

    public function view()
    {
        $__menu = 'products.categories';

        if (request('i')) {
            $cat = \Ds\Models\ProductCategory::findWithPermission(request('i'));
            $title = $cat->name;
            $action = '/jpanel/products/update';
        } else {
            $cat = \Ds\Models\ProductCategory::newWithPermission();
            $title = 'New Category';
            $action = '/jpanel/products/insert';
        }

        pageSetup($title, 'jpanel');

        /* membership ids required */
        $membership_ids_required = membership_access_get_by_parent('product_category', $cat->id);
        $membership_list = [];
        if (count($membership_ids_required) > 0) {
            foreach ($membership_ids_required as $membership_id) {
                $membership_list[] = membership_get($membership_id);
            }
        }

        $schemas = app('theme')->getTemplateMetadata('collection');

        return $this->getView('product_categories/view', compact(
            '__menu',
            'cat',
            'title',
            'action',
            'membership_ids_required',
            'membership_list',
            'schemas',
        ));
    }
}
