<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('requires.feature:givecloud_pro');
    }

    public function results($terms = null)
    {
        $terms = urldecode($terms);

        $category = request('category', null);

        if (empty($terms) && request()->filled('keywords')) {
            $terms = request('keywords');

            $query = collect(request()->query->all())
                ->reject(function ($value, $key) {
                    return $key === 'keywords' || empty($value);
                });

            if (count($query)) {
                return redirect()->to('search/' . urlencode($terms) . '?' . http_build_query($query->all()));
            }

            return redirect()->to('search/' . urlencode($terms));
        }

        $results = collect();

        /*
            'type'          => '',
            'rank'          => 0.0,
            'title'         => '',
            'excerpt'       => '',
            'feature_image' => '',
            'permalink'     => ''
        */

        $resultsHaveProducts = false;
        $resultsHavePages = false;
        $resultsHavePosts = false;
        $resultsHaveFundraisingPages = false;
        $resultsHaveSponsees = false;

        if ($terms) {
            // products
            $products = \Ds\Models\Product::with('photo')
                ->where(function ($query) use ($terms) {
                    $query->where('name', 'like', "%$terms%");
                    $query->orWhere('description', 'like', "%$terms%");
                    $query->orWhere('summary', 'like', "%$terms%");
                    $query->orWhere('code', 'like', "%$terms%");
                    $query->orWhereHas('variants', function ($query) use ($terms) {
                        $query->where('variantname', 'like', "%$terms%");
                    });
                })->active()
                ->take(25)
                ->get();

            if (count($products)) {
                $resultsHaveProducts = true;
                if (in_array($category, [null, 'products'])) {
                    $results = $results->merge($products);
                }
            }

            // pages
            $pages = \Ds\Models\Node::with('featuredImage')
                ->where(function ($query) use ($terms) {
                    $query->where('title', 'like', "%$terms%");
                    $query->orWhere('body', 'like', "%$terms%");
                })->where('isactive', 1)
                ->where('type', 'html')
                ->take(25)
                ->get();

            if (count($pages)) {
                $resultsHavePages = true;
                if (in_array($category, [null, 'pages'])) {
                    $results = $results->merge($pages);
                }
            }

            // posts
            $posts = \Ds\Models\Post::with('featuredImage')
                ->where(function ($query) use ($terms) {
                    $query->where('post.name', 'like', "%$terms%");
                    $query->orWhere('post.description', 'like', "%$terms%");
                    $query->orWhere('post.body', 'like', "%$terms%");
                })->active()
                ->take(25)
                ->get();

            if (count($posts)) {
                $resultsHavePosts = true;
                if (in_array($category, [null, 'posts'])) {
                    $results = $results->merge($posts);
                }
            }

            // fundraising pages
            $fundraising_pages = \Ds\Models\FundraisingPage::with('photo')
                ->where(function ($query) use ($terms) {
                    $query->where('fundraising_pages.title', 'like', "%$terms%");
                    $query->orWhere('fundraising_pages.description', 'like', "%$terms%");
                })->active()
                ->websiteType()
                ->take(25)
                ->get();

            if (count($fundraising_pages)) {
                $resultsHaveFundraisingPages = true;
                if (in_array($category, [null, 'fundraisers'])) {
                    $results = $results->merge($fundraising_pages);
                }
            }

            // sponsees
            $sponsees = \Ds\Domain\Sponsorship\Models\Sponsorship::with('featuredImage')
                ->where(function ($query) use ($terms) {
                    $query->where(DB::raw("concat(first_name,' ',last_name)"), 'like', "%$terms%");
                    $query->orWhere('reference_number', 'like', "%$terms%");
                })->active()
                ->take(25)
                ->get();

            if (count($sponsees)) {
                $resultsHaveSponsees = true;
                if (in_array($category, [null, 'sponsees'])) {
                    $results = $results->merge($sponsees);
                }
            }
        }

        return $this->renderTemplate('search-results', [
            'terms' => $terms,
            'search_results' => Drop::collectionFactory($results->take(25), 'SearchResult'),
            'resultsHaveProducts' => $resultsHaveProducts,
            'resultsHavePages' => $resultsHavePages,
            'resultsHavePosts' => $resultsHavePosts,
            'resultsHaveFundraisingPages' => $resultsHaveFundraisingPages,
            'resultsHaveSponsees' => $resultsHaveSponsees,
            'category' => $category,
        ]);
    }
}
