<?php

namespace Ds\Models\Passport;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasSiteScope
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::registerSiteScope();
        static::setSiteIdWhenSaving();
    }

    protected static function registerSiteScope(): void
    {
        static::addGlobalScope('site', function (Builder $builder) {
            $builder
                ->where('site_id', site()->id)
                ->orWhere(function (Builder $builder) {
                    $builder
                        ->whereNull('site_id')
                        ->whereNull('user_id');
                });
        });
    }

    protected static function setSiteIdWhenSaving(): void
    {
        static::creating(self::setSiteIdIfUser());
        static::saving(self::setSiteIdIfUser());
        static::updating(self::setSiteIdIfUser());
    }

    protected static function setSiteIdIfUser(): callable
    {
        return function (Model $oauthModel) {
            if ($oauthModel->user_id) {
                $oauthModel->site_id = site()->id;
            }
        };
    }
}
