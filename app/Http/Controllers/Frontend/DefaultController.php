<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Http\Controllers\API\WebhookController;
use Ds\Models\Node;
use Ds\Models\PostType;
use Ds\Models\Product;
use Ds\Repositories\DonationFormConfigRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class DefaultController extends Controller
{
    public function handleError($code)
    {
        return response($this->renderTemplate($code), $code);
    }

    public function handlePath($path = null)
    {
        $path = ltrim($path, '/');

        // throw 404 error for any non-existant backend URLs
        if (request()->is('jpanel/*')) {
            abort(404);
        }

        if (isGivecloudExpress()) {
            abort(404);
        }

        try {
            $out = $this->handleAlias($path)
                ?? $this->handlePage($path)
                ?? $this->handleProduct($path)
                ?? $this->handleCategory($path)
                ?? $this->handlePosts($path);

            if ($out) {
                return $out;
            }
        } catch (ModelNotFoundException $e) {
            // do nothing
        }

        return $this->handleError(404);
    }

    /**
     * Handle the homepage
     */
    public function handleHomePage()
    {
        if (isGivecloudExpress()) {
            $product = Product::donationForms()
                ->whereDefaultDonationForm()
                ->firstOrFail();

            return (new DonationFormsController)(app(DonationFormConfigRepository::class), $product->code);
        }

        $code = sys_get('product_as_homepage');

        if ($code) {
            return (new ProductsController)->callAction('show', [$code, '']);
        }

        return $this->handlePath();
    }

    /**
     * Check for and handle a matching page.
     */
    private function handlePage($path)
    {
        $id = DB::table('node')
            ->whereIn('node.type', ['html', 'advanced', 'liquid'])
            ->where('node.isactive', 1);

        if (empty($path)) {
            if (request('wc-api') === 'WC_Gateway_Paypal') {
                return (new WebhookController)->callAction('postPaypal', []);
            }

            $id->where('code', 'home');
        } else {
            $id->whereIn('node.url', [$path, "/$path", "$path.php", "/$path.php"]);
        }

        $id = $id->value('node.id');

        if ($id) {
            remove_php_extension_from_url($path);

            $page = Node::find($id);

            if ($page->isChildOfDonorPortalMenu()) {
                $template = 'accounts/custom-page';
            } else {
                $template = "page.{$page->template_suffix}";
            }

            if ($page->template_suffix === 'none') {
                return $page->toLiquid()->invokeDrop('content');
            }

            pageSetup($page->pagetitle, null, $page->id);

            return $this->renderTemplate($template, compact('page'));
        }
    }

    /**
     * Check for and handle a matching product.
     */
    private function handleProduct($path)
    {
        if (preg_match('#^products?/([^/]+)/[^/]+$#', $path, $matches)) {
            $code = $matches[1];
        } else {
            $code = DB::table('product')
                ->whereIn('permalink', [$path, "/$path"])
                ->value('code');
        }

        if ($code) {
            return (new ProductsController)->callAction('show', [$code, '']);
        }
    }

    /**
     * Check for and handle a matching category.
     */
    private function handleCategory($path)
    {
        $url_name = preg_match('#^products/(clearance|featured|new)$#i', $path, $matches)
            ? $matches[1]
            : DB::table('productcategory')
                ->whereIn('url_name', [$path, "/$path"])
                ->value('url_name');

        if ($url_name) {
            return (new ProductsController)->callAction('listByCategory', [$url_name]);
        }
    }

    /**
     * Check for and handle posts.
     */
    private function handlePosts($path)
    {
        $segments = explode('/', $path, 3);

        try {
            $postType = PostType::select()
                ->where('sysname', 'blog')
                ->where('url_slug', $segments[0])
                ->firstOrFail();

            if (count($segments) > 2 && $segments[1] == 'categories') {
                return (new FeedsController)->callAction('handlePostTypeCategory', [$segments[2], $postType]);
            }
            if (count($segments) > 1) {
                return (new FeedsController)->callAction('handlePost', [$segments[1], $postType]);
            }

            return (new FeedsController)->callAction('handlePostType', [$postType]);
        } catch (ModelNotFoundException $e) {
            return;
        }
    }

    /**
     * Check for and handle a redirect alias.
     */
    private function handleAlias($path)
    {
        $alias = DB::table('aliases')
            ->where('source', $path)
            ->orWhere('source', '*')
            ->first();

        if ($alias) {
            if ($alias->type === 'html') {
                return view('frontend.redirect', ['url' => url($alias->alias)]);
            }

            return redirect()->to($alias->alias, $alias->status_code);
        }
    }
}
