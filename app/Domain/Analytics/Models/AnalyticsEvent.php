<?php

namespace Ds\Domain\Analytics\Models;

use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AnalyticsEvent extends Model
{
    public function analyticsVisit(): BelongsTo
    {
        return $this->belongsTo(AnalyticsVisit::class);
    }

    public function eventable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOpensImpressionsOrPageviews(Builder $query, string $alias = null): void
    {
        if ($alias) {
            $alias = "$alias.";
        }

        $query->where(function (Builder $query) use ($alias) {
            $query->where(function (Builder $query) use ($alias) {
                $query->where("{$alias}event_category", 'fundraising_forms.modal_embed');
                $query->where("{$alias}event_name", 'open');
            })->orWhere(function (Builder $query) use ($alias) {
                $query->where("{$alias}event_category", 'fundraising_forms.hosted_page');
                $query->where("{$alias}event_name", 'pageview');
            })->orWhere(function (Builder $query) use ($alias) {
                $query->where("{$alias}event_category", 'fundraising_forms.inline_embed');
                $query->where("{$alias}event_name", 'impression');
            });
        });
    }
}
