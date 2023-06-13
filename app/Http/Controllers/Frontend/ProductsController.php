<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Models\Product;
use Ds\Models\ProductCategory as Category;
use Ds\Repositories\AccountTypeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('requires.feature:givecloud_pro');
    }

    /**
     * Public product list.
     *
     * /products/search/{terms}
     *
     * @return string
     */
    public function search($terms = '')
    {
        if (request()->filled('keywords')) {
            $terms = request('keywords');

            $query = collect(request()->query->all())
                ->reject(function ($value, $key) {
                    return $key === 'keywords' || empty($value);
                });

            if (count($query)) {
                return redirect()->to('products/search/' . urlencode($terms) . '?' . http_build_query($query->all()));
            }

            return redirect()->to('products/search/' . urlencode($terms));
        }

        request()->query->set('keywords', urldecode($terms));

        return $this->listByCategory('search');
    }

    /**
     * Public product list.
     *
     * /products/{category_slug}
     * /products/{category_slug}.php
     * /products/search
     * /products/search.php
     *
     * V1
     * /products/new, /clearance, /featured
     *
     * @return \Illuminate\Http\RedirectResponse|string
     */
    public function listByCategory($category_slug)
    {
        remove_php_extension_from_url($category_slug, true);

        // params
        $keywords = request()->nonArrayInput('keywords', '');
        $filter = request()->nonArrayInput('filter', '');
        $order_by = Str::snake(strtolower(sys_get('category_default_order_by')));
        $order_by = request('order_by', $order_by);

        // search w/ no keywords
        if ($category_slug == 'search' && (! $keywords && ! $filter)) {
            abort(404, __('frontend/collection.missing_search_text'));
        }

        // base query
        $product_list = Product::query()
            ->select([
                'product.*',
                DB::raw('productcategory.name as categoryname'),
                DB::raw('(IF(IFNULL(defaultVariant.saleprice, 0) > 0, defaultVariant.saleprice, IFNULL(defaultVariant.price, 0)) + IFNULL(SUM(linkedVariant.qty*linkedVariant.price), 0)) as actualprice'),
            ])->join('productinventory as defaultVariant', function ($join) {
                $join->on('defaultVariant.productid', '=', 'product.id');
                $join->where('defaultVariant.isdefault', 1);
                $join->where('defaultVariant.is_deleted', 0);
            })->leftJoin('variant_variant as linkedVariant', 'linkedVariant.variant_id', '=', 'defaultVariant.id')
            ->join('productcategorylink', 'productcategorylink.productid', '=', 'product.id')
            ->join('productcategory', 'productcategory.id', '=', 'productcategorylink.categoryid')
            ->with([
                'customFields',
                'defaultVariant',
                'defaultVariant.linkedVariants',
                'defaultVariant.linkedVariants.linkedVariants',
                'defaultVariant.linkedVariants.quantityModifiedBy',
                'defaultVariant.quantityModifiedBy',
                'photo',
                'variants',
                'variants.linkedVariants',
                'variants.linkedVariants.linkedVariants',
                'variants.linkedVariants.quantityModifiedBy',
                'variants.quantityModifiedBy',
            ])->active()
            ->groupBy('product.id');

        // get category
        $category = Category::where('url_name', $category_slug)->first();

        if ($category) {
            if (! member() && $category->isSecuredByMembership()) {
                session()->put('url.website_intended', request()->fullUrl());

                return redirect()->to('/account/login');
            }

            $category = Category::securedByMembership()->find($category->id);
        }

        // category list
        if ($category) {
            $product_list->where('productcategorylink.categoryid', $category->id);

        // search or flagged products
        } elseif (in_array($category_slug, ['search', 'new', 'featured', 'clearance'])) {
            // flags
            if ($category_slug == 'new') {
                $product_list->where('isnew', 1);
            }

            if ($category_slug == 'featured') {
                $product_list->where('isfeatured', 1);
            }

            if ($category_slug == 'clearance') {
                $product_list->where('isclearance', 1);
            }

            // else 404
        } else {
            abort(404, __('frontend/collection.category_not_found'));
        }

        // keyword search
        if ($keywords) {
            // search
            $product_list->where(function ($query) use ($keywords) {
                $query->where('product.name', 'LIKE', '%' . $keywords . '%');
                $query->orWhere('product.code', 'LIKE', '%' . $keywords . '%');
                $query->orWhere('product.author', 'LIKE', '%' . $keywords . '%');
            });
        }

        // search filter (author)
        if ($filter) {
            $product_list->where('product.author', $filter);
        }

        // membership restriction
        $product_list->securedByMembership();

        // order by (check against restricted keywords)
        $allowedOrderBy = [
            'name',
            'name_desc',
            'actualprice',
            'actualprice_desc',
            'price',
            'price_desc',
            'author',
            'filter',
            'categoryname',
            'category_name',
            'publish_start_date',
            'publish_start_date_desc',
        ];

        if (in_array($order_by, $allowedOrderBy)) {
            if ($order_by == 'filter') {
                $sortBy = 'author';
            } elseif ($order_by == 'price') {
                $sortBy = 'actualprice';
            } elseif ($order_by == 'price_desc') {
                $sortBy = 'actualprice_desc';
            } elseif ($order_by == 'category_name') {
                $sortBy = 'categoryname';
            } elseif ($order_by == 'name' || $order_by == 'name_desc') {
                $sortBy = 'product.name';
            } else {
                $sortBy = $order_by;
            }

            // descending
            if (substr($sortBy, -4) === 'desc') {
                $product_list->orderBy(substr($sortBy, 0, -5), 'desc');

            // ascending
            } else {
                $product_list->orderBy($sortBy);
            }
        }

        $keywords = e($keywords);

        // decide page title
        if ($category) {
            $pageTitle = $category->name;
        } elseif ($category_slug === 'new') {
            $pageTitle = __('frontend/collection.new');
        } elseif ($category_slug === 'featured') {
            $pageTitle = __('frontend/collection.featured');
        } elseif ($category_slug === 'clearance') {
            $pageTitle = __('frontend/collection.clearance');
        } elseif ($category_slug === 'search' && $filter) {
            $pageTitle = $filter . '';
        } elseif ($category_slug === 'search' && $keywords) {
            $pageTitle = __('frontend/collection.search', compact('keywords'));
        } else {
            abort(404, __('frontend/collection.product_not_found'));
        }

        // legacy page setup
        pageSetup($pageTitle, 'productList');

        // filters (authors)
        // if its a category list, grab filters from category
        if ($category) {
            $filters = $category->filters;

        // otherwise, grab it from search results
        } else {
            $filters = (clone $product_list)
                ->select(DB::raw('trim(author) as name'))
                ->distinct()
                ->whereRaw("trim(author) != ''")
                ->orderByReset()
                ->orderBy('name')
                ->get();
            if ($filters) {
                foreach ($filters as $f) {
                    $f->name_shortened = Str::limit($f->name, 27);
                }
            }
        }

        if ($filters) {
            foreach ($filters as $f) {
                $f->is_selected = ($f->name == $filter);
            }
        }

        if ($category) {
            $templateSuffix = $category->template_suffix;
        } else {
            $templateSuffix = $category_slug;
        }

        $filters = [
            'order_by' => $order_by,
            'filters' => $filters,
            'keywords' => $keywords,
            'filter' => $filter,
            'is_search' => ($category_slug == 'search'),
            'is_featured' => ($category_slug == 'featured'),
            'is_clearance' => ($category_slug == 'clearance'),
            'per_page' => max(6, min(72, request('per_page') ?? 36)),
        ];

        $products = $product_list->paginate($filters['per_page']);

        // render template
        return $this->renderTemplate("collection.$templateSuffix", [
            'products' => $products,
            'pagination' => get_pagination_data($products, array_filter($filters)),
            'category' => $category,
            'page_name' => $pageTitle,
            'pageTitle' => $pageTitle,
            'filter' => (object) $filters,
        ]);
    }

    public function show($code, $slug)
    {
        remove_php_extension_from_url("products/$code/$slug");

        // get the product
        $productModel = \Ds\Models\Product::whereCode(urldecode($code))
            ->with('variants', 'defaultVariant', 'customFields', 'memberships')
            ->first();

        // was there an error?
        if ($productModel === false || $productModel->isenabled == 0) {
            return response($this->renderTemplate('404'), 404);
        }

        // if this product requires membership access
        if (feature('membership') && $productModel->memberships->count() > 0) {
            // if the member is NOT logged in
            if (! member()) {
                session()->put('url.website_intended', request()->fullUrl());

                return redirect()->to('/account/login');
            }

            // if member is logged in, make sure they belong to a
            // group that can access this product
            $matching_groups = $productModel->memberships->pluck('id')
                ->intersect(member()->activeGroups()->pluck('id'));

            if ($matching_groups->count() == 0) {
                return redirect()->to('/');
            }
        }

        // page build
        $pageTitle = $productModel->name . ' (' . $productModel->code . ')';

        // render shortcodes
        $productModel->description = do_shortcode($productModel->description, true);

        pageSetup($pageTitle, 'productList', 4);

        return $this->renderTemplate("product.{$productModel->template_suffix}", [
            'product' => $productModel,
            'account_types' => app(AccountTypeRepository::class)->getOnWebAccountTypeDrops(),
        ]);
    }
}
