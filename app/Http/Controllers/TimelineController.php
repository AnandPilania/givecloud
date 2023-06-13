<?php

namespace Ds\Http\Controllers;

use Carbon\Carbon;
use Ds\Models\Media;
use Ds\Models\Timeline;

class TimelineController extends Controller
{
    public function all($type, $id)
    {
        Carbon::setToStringFormat('M j, Y');

        return Timeline::whereParentType($type)
            ->where('parent_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('posted_on', 'desc')
            ->orderBy('id', 'desc')
            ->with('createdBy', 'media')
            ->get();
    }

    public function show(Timeline $timeline)
    {
        return $timeline->load('media');
    }

    public function store()
    {
        return $this->update(new Timeline);
    }

    public function update(Timeline $timeline)
    {
        $timeline->parent_type = request('parent_type') ?: $timeline->parent_type;
        $timeline->parent_id = request('parent_id') ?: $timeline->parent_id;
        $timeline->tag = request('tag') ?: $timeline->tag;
        $timeline->headline = request('headline') ?: $timeline->headline;
        $timeline->message = request('message') ?: $timeline->message;
        $timeline->posted_on = request('posted_on') ?: toUtc(Carbon::now());
        $timeline->is_private = (int) request('is_private');
        $timeline->save();

        $media = collect(request('media'))
            ->filter(function ($media) {
                return array_key_exists('id', $media);
            });

        if (count($media)) {
            $timeline->media()->sync($media->pluck('id')->all());

            foreach ($media as $data) {
                $item = Media::find($data['id']);
                $item->caption = $data['caption'];
                $item->save();
            }
        }

        if (request('notify_sponsors') && ! $timeline->is_private) {
            $sponsorship = \Ds\Domain\Sponsorship\Models\Sponsorship::where('id', $timeline->parent_id)->first();

            if ($sponsorship) {
                $sponsorship->activeSponsors()->with('member')->get()->each(function ($s) use ($sponsorship) {
                    $s->notify('sponsorship_timeline_updated', [
                        'sponsorship_url' => secure_site_url('/account/sponsorships/' . $s->id),
                    ]);
                });
            }
        }

        return $timeline->load('media');
    }

    public function media(Timeline $timeline)
    {
        return $timeline->media;
    }

    public function destroy(Timeline $timeline)
    {
        $timeline->delete();

        return $timeline;
    }
}
