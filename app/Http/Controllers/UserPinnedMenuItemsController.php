<?php

namespace Ds\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class UserPinnedMenuItemsController extends Controller
{
    public function __invoke(Request $request, Authenticatable $user)
    {
        /* @var $user \Ds\Models\User */
        $user->setMetadata('pinned-menu-items', $request->input('menuItems'));

        if ($user->save()) {
            $this->flash->success($user->full_name . ' updated successfully.');
        } else {
            $this->flash->error('An error occurred, please try again');
        }

        return redirect()->to(route('backend.profile'));
    }
}
