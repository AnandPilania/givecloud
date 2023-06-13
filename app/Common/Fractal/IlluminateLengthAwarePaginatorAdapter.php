<?php

namespace Ds\Common\Fractal;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\PaginatorInterface;

/**
 * A paginator adapter for LengthAwarePaginator
 *
 * @author Maxime Beaudoin <firalabs@gmail.com>
 */
class IlluminateLengthAwarePaginatorAdapter implements PaginatorInterface
{
    /**
     * The paginator instance.
     *
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected $paginator;

    /**
     * Create a new illuminate pagination adapter.
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @return void
     */
    public function __construct(LengthAwarePaginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->paginator->currentPage();
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function getLastPage()
    {
        return $this->paginator->lastPage();
    }

    /**
     * Get the total.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->paginator->total();
    }

    /**
     * Get the count.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->paginator->count();
    }

    /**
     * Get the number per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->paginator->perPage();
    }

    /**
     * Get the url for the given page.
     *
     * @param int $page
     * @return string
     */
    public function getUrl($page)
    {
        return $this->paginator->url($page);
    }

    /**
     * Get the paginator instance.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }
}
