<?php

namespace Tests\Unit\Domain\QuickStart\Tasks;

use Ds\Domain\QuickStart\Tasks\DonationItem;
use Ds\Models\Product;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** @group QuickStart */
class DonationItemTest extends TestCase
{
    public function testIsCompletedReturnsFalseWhenNoProduct(): void
    {
        Product::query()->withoutTemplates()->delete();

        $this->assertFalse($this->app->make(DonationItem::class)->isCompleted());
    }

    public function testIsCompletedReturnsFalseWhenProductHasHiddenReasons(): void
    {
        $product = $this->product();

        $product->isenabled = 0;
        $product->save();

        $this->assertFalse($this->app->make(DonationItem::class)->isCompleted());
    }

    /** @dataProvider incompleteReasonsTrimsDatesDataProvioder */
    public function testPotentialResolutionPathsTrimsDates(string $column, string $date, string $expected): void
    {
        $product = $this->product();

        $product->{$column} = Carbon::parse($date);
        $product->save();

        $this->assertSame($expected, $this->app->make(DonationItem::class)->potentialResolutionPaths()[0]);
    }

    public function incompleteReasonsTrimsDatesDataProvioder(): array
    {
        return [
            ['publish_start_date', 'next week', 'The active start date is set in the future'],
            ['publish_end_date', 'last week', 'The active end date is set in the past'],
        ];
    }

    public function testPotentialResolutionPathsModifiesStrings(): void
    {
        $product = $this->product();

        $product->categories()->sync([]);
        $product->isenabled = false;
        $product->template_suffix = '';
        $product->save();

        $this->assertSame('Some items are set to not display on the web, check the Options panel', $this->app->make(DonationItem::class)->potentialResolutionPaths()[0]);
        $this->assertSame('Some items do not belong to any categories', $this->app->make(DonationItem::class)->potentialResolutionPaths()[1]);
    }

    public function testIncompleteReasonsAreUnique(): void
    {
        $product = $this->product();

        $product->publish_start_date = Carbon::parse('next week');
        $product->save();

        $otherProduct = $product->replicate();
        $otherProduct->save();

        $this->assertCount(1, $this->app->make(DonationItem::class)->potentialResolutionPaths());
    }

    private function product(): Product
    {
        $products = Product::query()->withoutTemplates()->get();

        $products->splice(1)->each(function (Product $product) {
            $product->delete();
        });

        return $products->first();
    }
}
