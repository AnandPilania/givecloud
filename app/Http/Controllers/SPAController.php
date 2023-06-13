<?php

namespace Ds\Http\Controllers;

use Ds\Http\View\Composers\LayoutComposer;
use Ds\Repositories\AdminSidebarMenuRepository;
use Illuminate\Support\Facades\Route;

class SPAController extends Controller
{
    public function __invoke(LayoutComposer $layoutComposer, AdminSidebarMenuRepository $adminSidebarMenuRepository)
    {
        if ($redirect = $this->checkForRedirect($adminSidebarMenuRepository)) {
            return $redirect;
        }

        $view = view('spa.view')->with(['appSource' => 'SPA']);

        $layoutComposer->compose($view);

        return $view;
    }

    private function checkForRedirect(AdminSidebarMenuRepository $adminSidebarMenuRepository)
    {
        // If its the dashboard
        if (Route::currentRouteName() == 'backend.session.index') {
            if (sys_get('onboarding_flow')) {
                return redirect()->to('jpanel/onboard/start');
            }

            if (user()->can('dashboard')) {
                return false;
            }

            $menuItems = $adminSidebarMenuRepository->flat();

            foreach ($menuItems as $item) {
                if (isset($item['url'])) {
                    return redirect($item['url']);
                }

                foreach ($item['children'] as $child) {
                    if (isset($child['url'])) {
                        return redirect($child['url']);
                    }
                }
            }

            return redirect()->route('backend.profile');
        }

        return false;
    }
}
