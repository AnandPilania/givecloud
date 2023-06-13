<?php

namespace Ds\Http\Controllers;

class MessengerController extends Controller
{
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showMessenger()
    {
        return redirect()->to('jpanel/messenger/conversations');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function showConsole()
    {
        return view('messenger.console', [
            '__menu' => 'admin.advanced',
        ]);
    }
}
