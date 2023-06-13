<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Sponsorship\Models\Segment;
use Ds\Domain\Sponsorship\Models\SegmentItem;

class SegmentItemsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth');
        $this->middleware('requires.feature:sponsorship');
    }

    public function destroy()
    {
        user()->canOrRedirect('segment.edit');

        $segment = SegmentItem::findOrFail(request('id'));
        $segment->delete();

        return redirect()->to("jpanel/sponsorship/segments/items?i={$segment->segment_id}");
    }

    public function index()
    {
        user()->canOrRedirect('segment.edit');

        $segment = Segment::with('items')->findOrFail(request('i'));

        return $this->getView('segment_items/index', [
            '__menu' => 'sponsorship.fields',
            'segment' => $segment,
        ]);
    }

    public function restore()
    {
        user()->canOrRedirect('segment.edit');

        $segment = SegmentItem::withTrashed()->findOrFail(request('id'));
        $segment->restore();

        return redirect()->to("jpanel/sponsorship/segments/items?i={$segment->segment_id}");
    }

    public function save()
    {
        user()->canOrRedirect('segment.edit');

        // create record if it doesn't exist
        if (request('id')) {
            $item = SegmentItem::findOrFail(request('id'));
        } else {
            $item = new SegmentItem;
            $item->segment_id = request('segment_id');
        }

        // update item
        $item->name = request('name');
        $item->summary = request('summary');
        $item->latitude = request('latitude');
        $item->longitude = request('longitude');
        $item->link = request('link');
        $item->target = request('target');
        $item->save();

        return redirect()->to("jpanel/sponsorship/segments/items?i={$item->segment_id}");
    }

    public function view()
    {
        user()->canOrRedirect('segment.edit');

        // edit item
        if (request('i')) {
            $item = SegmentItem::with('segment')
                ->withTrashed()
                ->findOrFail(request('i'));

            $title = 'Edit ' . $item->name;

        // new item
        } else {
            $item = new SegmentItem;
            $item->segment = Segment::findOrFail(request('s'));

            $title = 'New Item';
        }

        return $this->getView('segment_items/view', [
            '__menu' => 'sponsorship.fields',
            'pageTitle' => $title,
            'item' => $item,
        ]);
    }
}
