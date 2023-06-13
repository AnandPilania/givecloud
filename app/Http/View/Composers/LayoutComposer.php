<?php

namespace Ds\Http\View\Composers;

use Ds\Repositories\AdminSpaConfigRepository;
use Illuminate\View\View;

class LayoutComposer
{
    /**
     * Bind data to the view.
     *
     * @param \Illuminate\View\View $view
     * @return void
     */
    public function compose(View $view)
    {
        if (! array_key_exists('__menu', $view->getData())) {
            $view->with('__menu', '');
        }

        $view->with([
            'appSource' => $appSource = $view->getData()['appSource'] ?? 'laravel',
            'adminSpaData' => app(AdminSpaConfigRepository::class)->get($appSource),
        ]);
    }
}
