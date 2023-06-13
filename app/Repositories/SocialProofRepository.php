<?php

namespace Ds\Repositories;

use Ds\Enums\SocialProofType;
use Ds\Http\Resources\SocialProofResource;
use Ds\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SocialProofRepository
{
    public function get(Product $product): array
    {
        $largestContributionItem = $this->getBuilder($product)
            ->addSelect(DB::raw(
                collect([
                    'IF(',
                    'productorderitem.recurring_frequency IS NOT NULL,',
                    'productorderitem.recurring_amount,',
                    'productorderitem.qty * productorderitem.price',
                    ') as contribution_amount',
                ])->join('')
            ))->orderByDesc('contribution_amount')
            ->first();

        if (empty($largestContributionItem)) {
            return [];
        }

        $contributionItems = $this->getBuilder($product)
            ->whereNotIn('productorderitem.id', [$largestContributionItem->id])
            ->orderByDesc('productorder.ordered_at')
            ->orderByDesc('productorder.id')
            ->take(14)
            ->get();

        return $contributionItems
            ->map(function ($contributionItem) {
                return SocialProofResource::make($contributionItem, SocialProofType::RECENT);
            })->add(SocialProofResource::make($largestContributionItem, SocialProofType::LARGEST_AMOUNT))
            ->shuffle()
            ->values()
            ->each->resolve()
            ->all();
    }

    private function getBuilder(Product $product): Builder
    {
        return $product->paidOrderItems()
            ->whereNull('productorder.refunded_at')
            ->getQuery()
            ->orderByReset();
    }
}
