<?php

namespace Ds\Domain\FeaturePreviews\PreviewCards;

use Ds\Domain\FeaturePreviews\Models\UserState;
use Ds\Services\ConfigService;
use Illuminate\Support\Str;

abstract class AbstractPreviewCard
{
    /** @var string */
    protected $key;

    public function description(): ?string
    {
        return null;
    }

    public function disable(): bool
    {
        $state = $this->state();
        $state->enabled = false;

        return $state->save();
    }

    public function enable(): bool
    {
        $state = $this->state();
        $state->enabled = true;

        return $state->save();
    }

    public function isDisabled(): bool
    {
        return ! $this->isEnabled();
    }

    public function isEnabled(): bool
    {
        return $this->isEnabledOrganizationWide()
            || $this->isEnabledForUser();
    }

    public function isEnabledForUser(): bool
    {
        return UserState::where('feature', $this->unprefixedKey())
            ->forCurrentUser()
            ->enabled()
            ->exists();
    }

    public function isEnabledOrganizationWide(): bool
    {
        return ((int) ConfigService::getInstance()->get($this->key(), false)) === 1;
    }

    public function key(): string
    {
        return $this->prefix() . $this->unprefixedKey();
    }

    public function links(): array
    {
        return [];
    }

    public function state(): UserState
    {
        return UserState::where('feature', $this->unprefixedKey())
            ->forCurrentUser()
            ->firstOrNew(
                ['feature' => $this->unprefixedKey(), 'user_id' => user('id')],
                ['enabled' => false]
            );
    }

    public function title(): ?string
    {
        return null;
    }

    public function unprefixedKey(): string
    {
        if (Str::startsWith($this->getKey(), $this->prefix())) {
            return Str::replaceFirst($this->prefix(), '', $this->getKey());
        }

        return $this->getKey();
    }

    protected function getKey(): string
    {
        return $this->key ?: Str::snake(class_basename($this));
    }

    protected function prefix(): string
    {
        return 'feature_';
    }
}
