<?php

namespace Ds\Domain\Flatfile\Services;

use Ds\Models\Product;
use Firebase\JWT\JWT;

class Contributions
{
    public function token(): string
    {
        return JWT::encode([
            'embed' => config('services.flatfile.embeds.contributions.id'),
            'user' => [
                'id' => auth()->user()->id,
                'email' => auth()->user()->email,
                'name' => auth()->user()->name,
            ],
            'org' => [
                'id' => site()->client->id,
                'name' => sys_get('ds_account_name'),
            ],
            'env' => [
                'account_name' => sys_get('ds_account_name'),
                'callback' => route('flatfile.webhook.contributions'),
                'form_experience_ids' => $this->donationForms(),
                'product_names' => $this->productCodesAndVariantNames(),
                'countries' => array_keys(cart_countries()),
            ],
        ], config('services.flatfile.embeds.contributions.key'), 'HS256');
    }

    public function productCodesAndVariantNames(): array
    {
        $names = collect();

        Product::query()
            ->withoutDonationForms()
            ->whereNotNull('code')
            ->with(['variants'])
            ->each(function (Product $product) use ($names) {
                $names->push($product->code);
                $product->variants->each(fn ($variant) => $names->push($product->code . '-' . $variant->variantname));
            });

        return $names->filter()
            ->values()
            ->toArray();
    }

    public function donationForms(): array
    {
        return Product::query()
            ->donationForms()
            ->get()
            ->map(fn (Product $product) => $product->hashid)
            ->toArray();
    }
}
