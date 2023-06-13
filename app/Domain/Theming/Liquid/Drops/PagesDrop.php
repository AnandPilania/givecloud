<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Repositories\PageRepository;

class PagesDrop extends Drop
{
    const SOURCE_REQUIRED = false;

    /**
     * Catch all method that is invoked before a specific method
     *
     * @param string $method
     * @return mixed
     */
    protected function liquidMethodMissing($method)
    {
        $pagesRepository = app(PageRepository::class);

        return is_numeric($method)
            ? $pagesRepository->find($method)
            : $pagesRepository->findByUrl((string) $method);
    }
}
