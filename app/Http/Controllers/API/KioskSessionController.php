<?php

namespace Ds\Http\Controllers\API;

use Ds\Domain\Kiosk\Models\Kiosk;
use Ds\Domain\Kiosk\Models\KioskSession;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class KioskSessionController extends Controller
{
    /**
     * @return array
     */
    public function startKioskSession(Kiosk $kiosk)
    {
        $session = session("kiosks:{$kiosk->id}:session");

        if ($session) {
            try {
                $session = KioskSession::findOrFail($session);
                $session->last_activity = fromUtc('now');
                $session->save();

                return compact('kiosk');
            } catch (ModelNotFoundException $e) {
                // do nothing
            }
        }

        $session = new KioskSession;
        $session->kiosk_id = $kiosk->id;
        $session->user_id = user('id');
        $session->last_activity = fromUtc('now');
        $session->device_platform = request('device_platform');
        $session->device_uuid = request('device_uuid');
        $session->device_manufacturer = request('device_manufacturer');
        $session->device_model = request('device_model');
        $session->device_version = request('device_version');
        $session->ip = request()->ip();
        $session->save();

        session(["kiosks:{$kiosk->id}:session" => $session->id]);

        return compact('kiosk');
    }
}
