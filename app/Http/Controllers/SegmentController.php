<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Sponsorship\Models\Segment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SegmentController extends Controller
{
    /**
     * A function to run every time this controller is used.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('requires.feature:sponsorship');
    }

    public function destroy(Request $request): RedirectResponse
    {
        user()->canOrRedirect('segment.edit');

        try {
            $segment = Segment::findOrFail($request->id);

            $segment->delete();
        } catch (ModelNotFoundException $e) {
            $this->flash->error("The segment to delete doesn't exist.");
        }

        // return to list
        return redirect()->to('jpanel/sponsorship/segments');
    }

    public function index()
    {
        // permission
        user()->canOrRedirect('segment');

        return $this->getView('segments/index', [
            '__menu' => 'sponsorship.fields',
            'pageTitle' => 'Custom Fields',
            'segments' => Segment::with('items')->get(),
        ]);
    }

    public function restore()
    {
        // permission
        user()->canOrRedirect('segment.edit');

        // restore record
        $segment = Segment::withTrashed()->where('id', request('id'))->first();
        $segment->restore();

        // return to list
        return redirect()->to('jpanel/sponsorship/segments/edit?i=' . request('id'));
    }

    public function save()
    {
        // permission
        user()->canOrRedirect('segment.edit');

        // create record if it doesn't exist
        if (request('id')) {
            $segment = Segment::findOrFail(request('id'));
        } else {
            $segment = new Segment;
        }

        $segment->name = request('name');
        $segment->name_plural = request('name_plural');
        $segment->description = request('description');
        $segment->is_geographic = (request('is_geographic') == 1);
        $segment->sequence = request('sequence');
        $segment->type = request('type', 'text');

        if (request('type') == 'text') {
            $segment->is_text_only = 1;
            $segment->is_simple = 1;
        } elseif (request('type') == 'multi-select') {
            $segment->is_text_only = 0;
            $segment->is_simple = 1;
        } elseif (request('type') == 'advanced-multi-select') {
            $segment->is_text_only = 0;
            $segment->is_simple = 0;
        }

        if (request('visibility') == '0') {
            $segment->show_as_filter = 1;
            $segment->show_in_detail = 1;
        } elseif (request('visibility') == '1') {
            $segment->show_as_filter = 0;
            $segment->show_in_detail = 1;
        } elseif (request('visibility') == '2') {
            $segment->show_as_filter = 0;
            $segment->show_in_detail = 0;
        }

        // TEMPORARY - dates can only be private this is because of the
        // theming implications to having date public fields and filters
        if ($segment->type == 'date') {
            $segment->show_as_filter = 0;
            $segment->show_in_detail = 0;
        }

        if ($segment->exists && $segment->isDirty('type')) {
            dispatch(new \Ds\Domain\Sponsorship\Jobs\ChangeSegmentType(
                $segment->id,
                $segment->getOriginal('type'),
                $segment->type
            ));
        }

        // update segment
        $segment->save();

        // if we're moving on to edit items, redirect to items
        if (request('_edit_items') == 1) {
            return redirect()->to('jpanel/sponsorship/segments/items?i=' . request('id'));
        }

        // otherwise, go to segment list

        return redirect()->to('jpanel/sponsorship/segments');
    }

    public function view()
    {
        // permission
        user()->canOrRedirect('segment');

        // existing segment
        if (request('i')) {
            $segment = Segment::withTrashed()->where('id', request('i'))->firstOrFail();
            $title = $segment->name;

        // NEW segment
        } else {
            $segment = new Segment;
            $segment->type = 'text';
            $title = 'Add Custom Field';
        }

        // return view
        return $this->getView('segments/view', [
            '__menu' => 'sponsorship.fields',
            'pageTitle' => $title,
            'segment' => $segment,
        ]);
    }
}
