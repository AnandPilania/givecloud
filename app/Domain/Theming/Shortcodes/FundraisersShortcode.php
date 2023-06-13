<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Ds\Models\FundraisingPage;
use Illuminate\Database\Eloquent\Builder;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class FundraisersShortcode extends Shortcode
{
    /** @var array */
    protected $ids = [];

    /** @var int */
    protected $limit = 100;

    /**
     * Output the posts template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $ids = $s->getParameter('ids', '');
        $product = $s->getParameter('product', '');
        $categories = $s->getParameter('categories', '');
        $orderby = $s->getParameter('orderby', 'activated_date');
        $order = $s->getParameter('order', 'desc');
        $limit = $s->getParameter('limit', '');

        $query = FundraisingPage::query()
            ->websiteType()
            ->activeAndVerified();

        $this->applyScopeForIds($query, $ids);
        $this->applyScopeForProduct($query, $product);
        $this->applyScopeForCategories($query, $categories);
        $this->applyOrderBy($query, $orderby, $order);
        $this->applyLimit($query, $limit);

        $fundraisingPages = $query->get();

        $template = new \Ds\Domain\Theming\Liquid\Template('templates/shortcodes/fundraisers');

        return $template->render([
            'id' => uniqid('fundraisers-shortcode-'),
            'fundraising_pages' => $fundraisingPages,
        ]);
    }

    protected function hasIds(): bool
    {
        return count($this->ids) > 0;
    }

    protected function applyScopeForIds(Builder $query, string $ids): void
    {
        $this->ids = collect(explode(',', $ids))
            ->map(fn ($id) => trim($id))
            ->reject(fn ($id) => empty($id))
            ->values()
            ->all();

        $query->when(count($this->ids), fn ($query) => $query->whereIn('id', $this->ids));
    }

    protected function applyScopeForProduct(Builder $query, string $product): void
    {
        $query->when($product, fn ($query) => $query->where('product_id', $product));
    }

    protected function applyScopeForCategories(Builder $query, string $categories): void
    {
        $categories = collect(explode(',', $categories))
            ->map(fn ($category) => trim($category))
            ->reject(fn ($category) => empty($category));

        $query->when($categories->isNotEmpty(), fn ($query) => $query->whereIn('category', $categories));
    }

    protected function applyOrderBy(Builder $query, string $column, string $direction): void
    {
        if ($this->hasIds()) {
            $query->orderBySet('id', $this->ids);

            return;
        }

        if (! in_array($column, ['activated_date', 'goal_deadline', 'goal_progress'])) {
            $column = 'activated_date';
        }

        if ($column === 'goal_progress') {
            $column = 'progress_percent';
        }

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $query->orderBy($column, $direction);
    }

    protected function applyLimit(Builder $query, string $limit): void
    {
        $query->take((int) min($limit ?: $this->limit, $this->limit));
    }
}
