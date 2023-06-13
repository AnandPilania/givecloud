<?php

namespace Ds\Domain\FeaturePreviews\Concerns;

use Ds\Domain\FeaturePreviews\Models\UserState;
use Ds\Domain\FeaturePreviews\Models\UserStateActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasActivities
{
    public static function bootHasActivities()
    {
        static::updating(function (UserState $model) {
            $model->logActivity();
        });
    }

    public function activities(): HasMany
    {
        return $this->hasMany(UserStateActivity::class, 'feature', 'feature')
            ->where('user_id', user('id'));
    }

    protected function logActivity(): bool
    {
        return (new UserStateActivity)->fill([
            'user_id' => user('id'),
            'feature' => $this->feature,
            'changes' => $this->getDirty(),
        ])->save();
    }
}
