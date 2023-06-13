<?php

namespace Ds\Http\Controllers\API;

use Ds\Domain\Kiosk\Models\Kiosk;

class KioskController extends Controller
{
    /**
     * @return array
     */
    public function getKiosks()
    {
        return [
            'kiosks' => Kiosk::all(),
        ];
    }

    /**
     * @return array
     */
    public function createKiosk()
    {
        $kiosk = new Kiosk;
        $kiosk->enabled = false;
        $kiosk->name = request('name');
        $kiosk->product_id = request('product_id');
        $kiosk->save();

        return compact('kiosk');
    }

    /**
     * @param \Ds\Domain\Kiosk\Models\Kiosk $kiosk
     * @return array
     */
    public function getKiosk(Kiosk $kiosk)
    {
        return compact('kiosk');
    }

    /**
     * @param \Ds\Domain\Kiosk\Models\Kiosk $kiosk
     * @return array
     */
    public function updateKiosk(Kiosk $kiosk)
    {
        $kiosk->name = request('name');
        $kiosk->product_ids = request('product_ids');
        $kiosk->enabled = request('enabled');
        $kiosk->config = request('config');
        $kiosk->save();

        return compact('kiosk');
    }

    /**
     * @param \Ds\Domain\Kiosk\Models\Kiosk $kiosk
     * @return array
     */
    public function deleteKiosk(Kiosk $kiosk)
    {
        $kiosk->delete();

        return ['success' => true];
    }
}
