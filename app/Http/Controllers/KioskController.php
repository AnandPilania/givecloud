<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Kiosk\Models\Kiosk;

class KioskController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function showKiosks()
    {
        user()->canOrRedirect('kiosk.view');

        pageSetup('Kiosks', 'jpanel');

        return $this->getView('kiosks/index', [
            '__menu' => 'kiosks',
            'kiosks' => Kiosk::all(),
        ]);
    }

    /**
     * @param \Ds\Domain\Kiosk\Models\Kiosk $kiosk
     * @return \Illuminate\View\View
     */
    public function showKiosk(Kiosk $kiosk)
    {
        user()->canOrRedirect('kiosk.view');

        pageSetup('Kiosk', 'jpanel');

        return $this->getView('kiosks/view', [
            '__menu' => 'kiosks',
            'kiosk' => $kiosk,
        ]);
    }
}
