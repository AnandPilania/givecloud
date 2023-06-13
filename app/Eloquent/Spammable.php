<?php

namespace Ds\Eloquent;

/** @mixin \Ds\Illuminate\Database\Eloquent\Model */
trait Spammable
{
    /**
     * Boot the spammable trait for a model.
     *
     * @return void
     */
    public static function bootSpammable()
    {
        $spammableByDefault = static::spammableByDefault();

        static::addGlobalScope(new SpammableScope($spammableByDefault));
    }

    public function getIsSpamColumn(): string
    {
        return 'is_spam';
    }

    /**
     * Get the fully qualified "is spam" column.
     *
     * @return string
     */
    public function getQualifiedIsSpamColumn()
    {
        return $this->qualifyColumn($this->getIsSpamColumn());
    }

    public static function spammableByDefault(): bool
    {
        return true;
    }
}
