<?php

namespace Ds\Common\Fractal;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Manager as BaseManager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope;

class Manager extends BaseManager
{
    /**
     * Create Array.
     *
     * Main method to kick this all off. Make a resource then pass it over, and use toArray()
     *
     * @param \League\Fractal\Resource\ResourceInterface $resource
     * @param string $scopeIdentifier
     * @param \League\Fractal\Scope $parentScopeInstance
     * @return array
     */
    public function createArray(ResourceInterface $resource, $scopeIdentifier = null, Scope $parentScopeInstance = null)
    {
        if ($resource instanceof Collection) {
            $data = $resource->getData();

            if (is_object($data) && $data instanceof LengthAwarePaginator) {
                $resource->setPaginator(new IlluminateLengthAwarePaginatorAdapter($data));
            }
        }

        return parent::createData($resource, $scopeIdentifier, $parentScopeInstance)->toArray();
    }
}
