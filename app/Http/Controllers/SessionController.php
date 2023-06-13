<?php

namespace Ds\Http\Controllers;

use Ds\Domain\MissionControl\MissionControlService;
use Ds\Models\User;
use Ds\Repositories\ChargebeeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SessionController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth', ['except' => [
            'login',
            'unlock_site',
        ]]);
    }

    public function login()
    {
        return redirect()->route('login');
    }

    public function invoice($invoiceId, MissionControlService $missioncontrol)
    {
        $invoice = $missioncontrol->getInvoice($invoiceId);

        if (! $invoice) {
            $this->flash->error('The invoice could not be found.');

            return redirect()->to('jpanel');
        }

        return view('sessions.invoice', [
            'invoice' => $invoice,
        ]);
    }

    public function logout()
    {
        auth()->logout();

        session()->flush();
        session()->regenerateToken();

        return redirect()->to('jpanel');
    }

    public function unlock_site()
    {
        session(['site_password' => request('site_password')]);

        return redirect()->websiteIntended('/');
    }

    public function getUrls()
    {
        // static urls:

        // /sponsorship
        $urls[] = (object) [
            'type' => 'page',
            'name' => 'Sponsorship',
            'url' => '/sponsorship',
        ];

        // create a p2p page
        $urls[] = (object) [
            'type' => 'page',
            'name' => 'Create a Fundraising Page',
            'url' => '/fundraisers/create',
        ];

        // list all p2p pages
        $urls[] = (object) [
            'type' => 'page',
            'name' => 'List of Fundraising Pages',
            'url' => '/fundraisers/all',
        ];

        // pages/menu items
        foreach (\Ds\Models\Node::whereIn('type', ['html', 'advanced'])->where('isactive', 1)->orderBy('title')->get() as $node) {
            $urls[] = (object) [
                'type' => 'page',
                'name' => $node->title,
                'url' => $node->abs_url,
            ];
        }

        // products
        foreach (\Ds\Models\Product::orderBy('name')->where('is_deleted', 0)->get() as $product) {
            $urls[] = (object) [
                'type' => 'product',
                'name' => $product->name,
                'url' => $product->url,
            ];
        }

        // categories
        foreach (\Ds\Models\ProductCategory::orderBy('name')->get() as $category) {
            $urls[] = (object) [
                'type' => 'category',
                'name' => $category->name,
                'url' => $category->url,
            ];
        }

        // blog posts
        foreach (\Ds\Models\Post::where('type', 2)->where('isenabled', true)->orderBy('name')->get() as $post) {
            $urls[] = (object) [
                'type' => 'post',
                'name' => $post->name,
                'url' => $post->this_url,
            ];
        }

        // blogs
        foreach (\Ds\Models\PostType::where('sysname', 'blog')->orderBy('name')->get() as $blog) {
            $urls[] = (object) [
                'type' => 'post',
                'name' => $blog->name,
                'url' => $blog->absolute_url,
            ];
        }

        return response($urls);
    }

    public function getProducts()
    {
        $products = DB::table('product as p')
            ->select([
                'p.id',
                'p.name',
                'p.code',
                'p.isenabled',
                'p.media_id',
                DB::raw('max(v.is_donation) as has_donation_variant'),
            ])->leftJoin('productinventory as v', 'v.productid', 'p.id')
            ->groupBy('p.id')
            ->orderBy('p.name')
            ->where('p.is_deleted', 0)
            ->where('v.is_deleted', 0);

        // products
        foreach ($products->lazy() as $product) {
            $product_json[] = (object) [
                'name' => $product->name,
                'code' => $product->code,
                'isenabled' => (bool) $product->isenabled,
                'id' => $product->id,
                'thumbnail' => media_thumbnail($product->media_id),
                'has_donation_variant' => (bool) $product->has_donation_variant,
            ];
        }

        return response($product_json);
    }

    public function getVariants()
    {
        $variants = \Ds\Models\Variant::select('productinventory.*', DB::raw('product.code'), DB::raw('product.name'), DB::raw('product.media_id'))
            ->join('product', 'product.id', '=', 'productinventory.productid')
            ->whereNull('product.deleted_at')
            ->orderBy('product.name')
            ->orderBy('productinventory.variantname');

        $variants = $variants->get();

        // products
        foreach ($variants as $variant) {
            $variant_json[] = (object) [
                'name' => $variant->name,
                'code' => $variant->code,
                'variant_name' => $variant->variantname,
                'price' => (float) $variant->actual_price,
                'id' => (int) $variant->id,
                'thumbnail' => media_thumbnail($variant->media_id),
                'is_donation' => $variant->is_donation,
            ];
        }

        return response($variant_json);
    }

    private function _random_quote()
    {
        $quotes = [
            [
                'quote' => 'Education is the most powerful weapon which you can use to change the world.',
                'author' => 'Nelson Mandela',
            ],
            [
                'quote' => 'Music can change the world because it can change people.',
                'author' => 'Bono',
            ],
            [
                'quote' => 'No matter what people tell you, words and ideas can change the world.',
                'author' => 'Robin Williams',
            ],
            [
                'quote' => 'If you don\'t like something, change it. If you can\'t change it, change your attitude.',
                'author' => 'Maya Angelou',
            ],
            [
                'quote' => 'To improve is to change; to be perfect is to change often.',
                'author' => 'Winston Churchill',
            ],
            [
                'quote' => 'Some people don\'t like change, but you need to embrace change if the alternative is disaster.',
                'author' => 'Elon Musk',
            ],
            [
                'quote' => 'Miracles happen everyday, change your perception of what a miracle is and you\'ll see them all around you.',
                'author' => 'Jon Bon Jovi',
            ],
            [
                'quote' => 'The best preparation for tomorrow is doing your best today.',
                'author' => 'H. Jackson Brown, Jr.',
            ],
            [
                'quote' => 'The best way out is always through.',
                'author' => 'Robert Frost',
            ],
            [
                'quote' => 'Nothing is impossible, the word itself says \'I\'m possible\'!',
                'author' => 'Aubrey Hepburn',
            ],
            [
                'quote' => 'Believe you can and you\'re halfway there.',
                'author' => 'Theodore Roosevelt',
            ],
        ];

        return $quotes[array_rand($quotes)];
    }

    /**
     * Runs everything the chargbee portal is closed.
     */
    public function chargebeeUpdated(ChargebeeRepository $chargebeeRepo, MissionControlService $missioncontrol)
    {
        $chargebeeRepo->flushCache();

        if (! $chargebeeRepo->hasValidPaymentSource()) {
            return redirect()->back();
        }

        if ($chargebeeRepo->hasPastDueBalance()) {
            $url = MissionControlService::getMissionControlApiUrl('collect-client/' . site()->client_id);
            Http::withToken(config('services.missioncontrol.api_token'))->post($url);
        }

        // Sub as a past due balance but has a valid payment source, snooze billing warning for 10 days
        // to give ACH time to process
        if ($chargebeeRepo->hasPastDueBalance()) {
            User::query()->update(['billing_warning_suppression_expiry_date' => now()->addDays(10)]);
        }

        $user = auth()->user();

        $message = "Past Due Payment: Method Updated\n\n" . $user->fullname . ' chose to update their payment method.';
        $missioncontrol->addNote($message);

        $this->flash->success("🎉 Yay! You're all set!");

        // go back to the page that the modal was displayed on
        return redirect()->back();
    }
}
