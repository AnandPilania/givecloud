<?php

namespace Ds\Domain\Analytics;

use Ds\Domain\Analytics\Models\AnalyticsEvent;
use Ds\Domain\Analytics\Models\AnalyticsVisit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnalyticsService
{
    public function collectEvent(Model $eventable, array $event, ?Request $request = null): AnalyticsEvent
    {
        $visit = $this->firstOrCreateActiveVisit($event, $request ?? request());

        return $this->createEventForVisit($visit, $eventable, $event);
    }

    private function firstOrCreateActiveVisit(array $event, Request $request): AnalyticsVisit
    {
        if (empty($event['visitor_id'])) {
            $event['visitor_id'] = $request->input('visitor') ?? Str::uuid()->toString();
        }

        $visit = AnalyticsVisit::query()
            ->where('updated_at', '>', now()->subMinutes(config('givecloud.analytics.visit_timeout')))
            ->where('visitor_id', $event['visitor_id'])
            ->when(
                session('member_id'),
                fn ($query) => $query->where(function ($query) {
                    $query->whereNull('member_id');
                    $query->orWhere('member_id', session('member_id'));
                }),
                fn ($query) => $query->whereNull('member_id'),
            )->first();

        return $visit ?? $this->createVisit($event, $request);
    }

    private function createVisit(array $event, Request $request): AnalyticsVisit
    {
        $visit = new AnalyticsVisit([
            'visitor_id' => $event['visitor_id'],
            'config_user_agent' => $request->userAgent(),
            'location_ip' => $request->ip(),
            'location_language' => $request->getPreferredLanguage(),
            'referrer_url' => $request->referer(),
            'utm_source' => $event['utm_source'] ?? null,
            'utm_medium' => $event['utm_medium'] ?? null,
            'utm_content' => $event['utm_content'] ?? null,
            'utm_campaign' => $event['utm_campaign'] ?? null,
            'member_id' => session('member_id'),
            'created_at' => fromUtc($event['timestamp'] ?? 'now'),
        ]);

        $visit->setVisitorAttributes();
        $visit->save();

        return $visit;
    }

    private function createEventForVisit(AnalyticsVisit $visit, Model $eventable, array $event): AnalyticsEvent
    {
        $analyticsEvent = new AnalyticsEvent([
            'eventable_type' => $eventable->getMorphClass(),
            'eventable_id' => $eventable->getKey(),
            'event_name' => $event['event_name'],
            'event_category' => $event['event_category'],
            'event_value' => $event['event_value'] ?? null,
            'utm_source' => $event['utm_source'] ?? null,
            'utm_source' => $event['utm_source'] ?? null,
            'utm_medium' => $event['utm_medium'] ?? null,
            'utm_content' => $event['utm_content'] ?? null,
            'utm_campaign' => $event['utm_campaign'] ?? null,
            'created_at' => fromUtc($event['timestamp'] ?? 'now'),
        ]);

        $visit->analyticsEvents()->save($analyticsEvent);

        return $analyticsEvent;
    }
}
