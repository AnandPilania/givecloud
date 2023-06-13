<?php

namespace Ds\Http\Controllers\API;

use Ds\Enums\ProductType;
use Ds\Http\Requests\API\DonationFormStoreFormRequest;
use Ds\Http\Requests\API\DonationFormUpdateFormRequest;
use Ds\Http\Resources\DonationForms\DonationFormResource;
use Ds\Models\Product;
use Ds\Models\Variant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

class DonationFormController extends Controller
{
    protected function registerMiddleware(): void
    {
        $this->middleware('auth');
        $this->middleware('requires.feature:fundraising_forms');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        user()->canOrRedirect(['product.view']);

        $donationForms = QueryBuilder::for(
            Product::query()
                ->donationForms()
                ->when(
                    $request->has('archived'),
                    fn ($query) => $query->onlyTrashed()
                )
        )->orderByDesc('createddatetime')
            ->get();

        return DonationFormResource::collection($donationForms);
    }

    public function show(string $donationForm): DonationFormResource
    {
        user()->canOrRedirect(['product.view']);

        $product = Product::query()
            ->donationForms()
            ->hashid($donationForm)
            ->firstOrFail();

        return DonationFormResource::make($product);
    }

    public function store(DonationFormStoreFormRequest $request): DonationFormResource
    {
        // TODO: setting the name like this is just temporary measure
        // until name becomes editable on frontend by the user
        $count = Product::where('name', 'like', 'Main Donation Form%')->count();
        $formName = $count ? "Main Donation Form ($count)" : 'Main Donation Form';

        $product = Product::create([
            'type' => ProductType::DONATION_FORM,
            'name' => $formName,
            'isenabled' => false,
            'show_in_pos' => false,
            'is_dcc_enabled' => true,
            'is_tax_receiptable' => sys_get('tax_receipt_pdfs'),
            'goal_use_dpo' => false,
            'outofstock_allow' => true,
        ]);

        $product->code = $product->hashid;
        $product->save();

        $product->variants()->saveMany([
            (new Variant)->forceFill([
                'sequence' => 1,
                'variantname' => 'Today Only',
                'billing_period' => 'onetime',
                'isshippable' => false,
                'is_donation' => true,
            ]),
            (new Variant)->forceFill([
                'sequence' => 2,
                'variantname' => 'Monthly',
                'billing_period' => 'monthly',
                'is_donation' => true,
                'isshippable' => false,
                'isdefault' => true,
            ]),
        ]);

        return $this->updateDonationForm($product, $request);
    }

    public function update(DonationFormUpdateFormRequest $request, string $donationForm): DonationFormResource
    {
        $product = Product::query()
            ->donationForms()
            ->hashid($donationForm)
            ->firstOrFail();

        return $this->updateDonationForm($product, $request);
    }

    public function destroy(string $donationForm): JsonResponse
    {
        $product = Product::query()
            ->donationForms()
            ->hashid($donationForm)
            ->firstOrFail();

        if ($product->metadata('donation_forms_is_default_form')) {
            return response()->json([
                'error' => 'You cannot delete your default form, you must identity another form as default first.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (Product::query()->donationForms()->where('id', '!=', $product->getKey())->doesntExist()) {
            return response()->json([
                'error' => 'You must have at least one form.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json(
            [],
            $product->delete()
                ? Response::HTTP_OK
                : Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public function makeDefault(string $donationForm): DonationFormResource
    {
        $product = Product::query()
            ->donationForms()
            ->hashid($donationForm)
            ->firstOrFail();

        $product->metadata(['donation_forms_is_default_form' => true]);
        $product->save();

        $this->markAsOnlyDefaultForm($product);

        return DonationFormResource::make($product);
    }

    public function replicate(string $donationForm): DonationFormResource
    {
        $product = Product::query()
            ->donationForms()
            ->hashid($donationForm)
            ->firstOrFail();

        /** @var \Ds\Models\Product $clone */
        $clone = $product->makeACopy();
        $clone->code = $clone->hashid;
        $clone->metadata(['donation_forms_is_default_form' => false]);
        $clone->save();

        return DonationFormResource::make($clone);
    }

    public function restore(string $donationForm): DonationFormResource
    {
        $product = Product::query()
            ->donationForms()
            ->hashid($donationForm)
            ->withTrashed()
            ->firstOrFail();

        $product->restore();

        return DonationFormResource::make($product);
    }

    private function markAsOnlyDefaultForm(Product $product): void
    {
        Product::query()
            ->donationForms()
            ->whereHas('metadataRelation', function ($query) {
                $query->where('key', 'donation_forms_is_default_form')
                    ->where('value', true);
            })->where('id', '!=', $product->getKey())
            ->each(function (Product $product) {
                $product->setMetadata('donation_forms_is_default_form', false);
                $product->save();
            });
    }

    private function updateDonationForm(Product $product, DonationFormStoreFormRequest $request): DonationFormResource
    {
        $data = $request->getDonationFormData();

        $product->forceFill($data['product']);
        $product->metadata($data['metadata']);

        if ($product->metadata['donation_forms_is_default_form']) {
            $this->markAsOnlyDefaultForm($product);
        }

        $product->save();

        if (! sys_get('fundraising_forms_initial_edit_at') && ! is_super_user()) {
            sys_set('fundraising_forms_initial_edit_at', now()->toDateString());
        }

        return DonationFormResource::make($product);
    }
}
