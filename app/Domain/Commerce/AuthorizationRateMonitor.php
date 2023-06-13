<?php

namespace Ds\Domain\Commerce;

use Ds\Domain\Commerce\Mail\PaymentFailureSpikeDetected as IncidentMailable;
use Ds\Domain\Commerce\Notifications\PaymentFailureSpikeDetected as IncidentNotification;
use Ds\Models\MonitoringIncident;
use Ds\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class AuthorizationRateMonitor
{
    /**
     * Check the autorization rate.
     *
     * @return \Ds\Models\MonitoringIncident
     */
    public static function check(): ?MonitoringIncident
    {
        $incident = MonitoringIncident::query()
            ->incidentType('arm_below_threshold')
            ->open()
            ->first();

        if (static::isBelowThreshold()) {
            return static::triggerIncident($incident);
        }

        return static::recoverIncident($incident);
    }

    /**
     * @param \Ds\Models\MonitoringIncident $incident
     * @return \Ds\Models\MonitoringIncident
     */
    private static function triggerIncident(?MonitoringIncident $incident): MonitoringIncident
    {
        if (empty($incident)) {
            $incident = MonitoringIncident::create([
                'incident_type' => 'arm_below_threshold',
                'triggered_at' => now(),
                'action_taken' => sys_get('arm_immediate_action'),
            ]);

            if ($incident->action_taken === 'always_require_captcha') {
                sys_set('ss_auth_attempts', 0);
            } elseif ($incident->action_taken === 'stop_accepting_payments') {
                sys_set('public_payments_disabled', true);
                sys_set('public_payments_disabled_until', now()->addMinutes(20)->toApiFormat());
            }
        }

        static::handleNotifications($incident);

        return $incident;
    }

    /**
     * Handle sending out notifications.
     *
     * @param \Ds\Models\MonitoringIncident $incident
     */
    private static function handleNotifications(MonitoringIncident $incident)
    {
        $minutes = sys_get('arm_renotify_recipients');

        // Never renotify
        if ($incident->last_notified_at && empty($minutes)) {
            return;
        }

        // Wait to renotify
        if ($incident->last_notified_at && now()->addMinutes($minutes)->greaterThan($incident->last_notified_at)) {
            return;
        }

        // Send email notifications
        $incident->last_notified_at = now();
        $incident->save();

        $attempts = static::getEvaluationWindowData();
        $mailable = new IncidentMailable($incident, $attempts->failed);

        foreach (sys_get('list:arm_recipients') as $emailAddress) {
            Mail::to($emailAddress)->send(clone $mailable);
        }

        User::mailAccountAdmins(clone $mailable);

        // Send support notifications
        Notification::route('mail', config('mail.support.address'))
            ->route('slack', config('logging.channels.slack.url'))
            ->notify(new IncidentNotification($incident, $attempts->failed));
    }

    private static function recoverIncident(?MonitoringIncident $incident): ?MonitoringIncident
    {
        if ($incident) {
            $incident->recovered_at = now();
            $incident->save();
        }

        return $incident;
    }

    /**
     * Get the attempts data for the evaluation window.
     *
     * @return \stdClass
     */
    public static function getEvaluationWindowData()
    {
        return reqcache('arm:attempts', function () {
            return DB::table('payments')
                ->select([
                    DB::raw('COUNT(id) as total'),
                    DB::raw("SUM(IF(status IN ('succeeded','pending'), 1, 0)) as successful"),
                    DB::raw("SUM(IF(status IN ('failed'             ), 1, 0)) as failed"),
                ])->where('created_at', '>', now()->subMinutes(
                    sys_get('int:arm_evaluation_window')
                ))->whereNull('created_by')
                ->first();
        });
    }

    /**
     * Is the authorization rate below the threshold.
     *
     * @return bool
     */
    public static function isBelowThreshold(): bool
    {
        $attempts = static::getEvaluationWindowData();

        if (empty($attempts->total)) {
            return false;
        }

        if ($attempts->total < sys_get('int:arm_attempt_threshold')) {
            return false;
        }

        if (($attempts->successful / $attempts->total) >= sys_get('double:arm_rate_threshold')) {
            return false;
        }

        return true;
    }
}
