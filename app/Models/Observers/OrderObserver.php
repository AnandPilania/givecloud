<?php

namespace Ds\Models\Observers;

use Carbon\Carbon;
use Ds\Models\Member;
use Ds\Models\Order;
use Throwable;

class OrderObserver
{
    /**
     * Response to the creating event.
     *
     * @param \Ds\Models\Order $model
     * @return void
     */
    public function creating(Order $model)
    {
        // First we'll need to create a fresh query instance and touch the creation and
        // update timestamps on this model, which are maintained by us for developer
        // convenience. After, we will just continue saving these model instances.
        $model->createddatetime = new Carbon;

        // make sure this orders' dp_sync_order flag is set to the global setting
        // this determines whether oe not this order will be automatically synced to DP
        if (! isset($model->dp_sync_order)) {
            $model->dp_sync_order = (sys_get('dp_auto_sync_orders') == '1');
        }

        if (request()->cookie('gcr')) {
            $referral_member_id = request()->cookie('gcr');
            if (Member::where('id', $referral_member_id)->exists()) {
                $model->referred_by = $referral_member_id;
            }
        }

        if (sys_get('dcc_enabled') && ! sys_get('dcc_ai_is_enabled')) {
            $model->dcc_per_order_amount ??= (float) sys_get('dcc_cost_per_order');
            $model->dcc_rate ??= (float) sys_get('dcc_percentage');
        } else {
            $model->dcc_per_order_amount = 0;
            $model->dcc_rate = 0;
        }
    }

    public function deleting(Order $model): void
    {
        $model->ledgerEntries()->delete();
    }

    public function restored(Order $model): void
    {
        $model->ledgerEntries()->restore();
    }

    /**
     * Response to the saving event.
     *
     * @param \Ds\Models\Order $model
     * @return void
     */
    public function saving(Order $model)
    {
        // ip geography
        if ($model->isDirty('client_ip')) {
            if ($model->client_ip) {
                try {
                    $ip = app('geoip')->getLocationData($model->client_ip);
                    $model->ip_country = data_get($ip, 'iso_code');
                } catch (Throwable $e) {
                    $model->ip_country = null;
                }
            } else {
                $model->ip_country = null;
            }
        }

        // useragent details
        if ($model->isDirty('client_browser')) {
            if ($model->client_browser) {
                try {
                    $ua = $model->ua();
                    $model->ua_device_brand = data_get($ua, 'device.brand');
                    $model->ua_device_model = data_get($ua, 'device.model');
                    $model->ua_os = data_get($ua, 'os.family');
                    $model->ua_os_version = data_get($ua, 'os.major');
                    $model->ua_browser = data_get($ua, 'ua.family');
                    $model->ua_browser_version = data_get($ua, 'ua.major');
                } catch (Throwable $e) {
                    $model->ua_device_brand = null;
                    $model->ua_device_model = null;
                    $model->ua_os = null;
                    $model->ua_os_version = null;
                    $model->ua_browser = null;
                    $model->ua_browser_version = null;
                }
            } else {
                $model->ua_device_brand = null;
                $model->ua_device_model = null;
                $model->ua_os = null;
                $model->ua_os_version = null;
                $model->ua_browser = null;
                $model->ua_browser_version = null;
            }
        }
    }

    public function saved(Order $model): void
    {
        $model->createOrUpdateContribution();
    }
}
