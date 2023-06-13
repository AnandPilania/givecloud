<?php

namespace Ds\Domain\QuickStart\Tasks;

use Ds\Models\Product;
use Illuminate\Support\Str;

class DonationItem extends AbstractTask
{
    public function title(): string
    {
        return 'Create a Donation Page';
    }

    public function description(): string
    {
        return 'Choose to run a campaign or create your main donation page, allowing your supporters to easily commit to weekly, monthly or annual giving.';
    }

    public function knowledgeBase(): string
    {
        return 'https://help.givecloud.com/en/articles/3289997-creating-your-first-product';
    }

    public function action(): string
    {
        return route('backend.products.edit');
    }

    public function actionText(): string
    {
        return 'Create Page';
    }

    public function isCompleted(): bool
    {
        $products = Product::query()->withoutTemplates()->get();

        if ($products->isEmpty()) {
            return false;
        }

        // As long as we have 1 shown product, this should be marked as completed.
        foreach ($products as $product) {
            if (empty(product_get_hidden_reasons($product))) {
                return true;
            }
        }

        return false;
    }

    public function potentialResolutionPaths(): array
    {
        return Product::query()
            ->withoutTemplates()
            ->get()
            ->map(function (Product $product) {
                return product_get_hidden_reasons($product);
            })->flatten()
            ->map(function (string $reason) {
                if (Str::startsWith($reason, 'The active start date is set ')) {
                    return 'The active start date is set in the future';
                }

                if (Str::startsWith($reason, 'The active end date is set to')) {
                    return  'The active end date is set in the past';
                }

                if (Str::startsWith($reason, 'This item is set to NOT display on the web')) {
                    return 'Some items are set to not display on the web, check the Options panel';
                }

                if (Str::startsWith($reason, 'This item does not belong to any categories')) {
                    return 'Some items do not belong to any categories';
                }

                return $reason;
            })->unique()
            ->all();
    }
}
