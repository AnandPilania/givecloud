<?php

namespace Ds\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Scope;

class SpammableScope implements Scope
{
    /** @var bool */
    private $applySpammableByDefault;

    /**
     * All of the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = ['WithSpam', 'WithoutSpam', 'OnlySpam'];

    public function __construct(bool $applySpammableByDefault = true)
    {
        $this->applySpammableByDefault = $applySpammableByDefault;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, EloquentModel $model)
    {
        if ($this->applySpammableByDefault) {
            $builder->where($model->getQualifiedIsSpamColumn(), false);
        }
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add the with-spam extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addWithSpam(Builder $builder)
    {
        $builder->macro('withSpam', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-spam extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addWithoutSpam(Builder $builder)
    {
        $builder->macro('withoutSpam', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->where($model->getQualifiedIsSpamColumn(), false);

            return $builder;
        });
    }

    /**
     * Add the only-spam extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addOnlySpam(Builder $builder)
    {
        $builder->macro('onlySpam', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->where($model->getQualifiedIsSpamColumn(), true);

            return $builder;
        });
    }
}
