<?php

namespace Ds\Http\Controllers;

use DomainException;
use Ds\Domain\Flatfile\Services\Sponsorships;
use Ds\Domain\Shared\DataTable;
use Ds\Domain\Sponsorship\Models\PaymentOptionGroup;
use Ds\Domain\Sponsorship\Models\Segment;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Domain\Sponsorship\Models\SponsorshipSegment;
use Ds\Http\Requests\SponsorshipSaveFormRequest;
use Illuminate\Support\Facades\DB;
use LiveControl\EloquentDataTable\ExpressionWithName;

class SponsorshipController extends Controller
{
    /**
     * A function to run every time this controller is used.
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('requires.feature:sponsorship');
    }

    /**
     * A list of sponsorship records
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // permission
        user()->canOrRedirect('sponsorship');

        return view('sponsorships.index', [
            'flatfileToken' => app(Sponsorships::class)->token(),
            'customFields' => app(Sponsorships::class)->customFields(),
        ]);
    }

    /**
     * A list of sponsorship records
     *
     * @return \Illuminate\Http\Response;
     */
    public function index_ajax()
    {
        // permission
        user()->canOrRedirect('sponsorship');

        // generate data table
        $dataTable = new DataTable($this->_baseQueryWithFilters(), [
            'id',
            'reference_number',
            new ExpressionWithName("concat(COALESCE(last_name, ''), ', ', COALESCE(first_name,''))", '_last_name_first_name'),
            'gender',
            'birth_date',
            new ExpressionWithName('birth_date', 'col6'),
            'sponsor_count',
            'is_sponsored',
            'is_enabled',
            // off the grid
            'first_name',
            'last_name',
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($sponsorship) {
            return [
                dangerouslyUseHTML('<a href="/jpanel/sponsorship/' . e($sponsorship->id) . '" class="ds-tribute"><i class="fa fa-search"></i></a>'),
                e($sponsorship->reference_number),
                e($sponsorship->full_name_reverse),
                dangerouslyUseHTML(($sponsorship->gender == 'F') ? '<i class="fa text-pink fa-female"></i>' : (($sponsorship->gender == 'M') ? '<i class="fa text-info fa-male"></i>' : '')),
                e(($sponsorship->birth_date) ? $sponsorship->birth_date->format('M j, Y') : ''),
                e($sponsorship->age),
                e($sponsorship->sponsor_count),
                dangerouslyUseHTML(($sponsorship->is_sponsored) ? '<i class="fa fa-check"></i>' : ''),
                dangerouslyUseHTML(($sponsorship->is_enabled) ? '<i class="fa fa-check"></i>' : ''),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    public function export()
    {
        // extra time to do this streaming
        set_time_limit(5 * 60);

        // base query
        $sponsorships = $this->_baseQueryWithFilters();

        // add segments to eager load
        $sponsorships->with('allSegments.items');

        // build basic headers
        $headers = [
            'Reference', 'First Name', 'Last Name', 'Gender',
            'Birth Date', 'Age', 'Enrollment Date', 'Months Waiting',
            'Longitude', 'Latitude', 'Created', 'Biography', 'Private Notes',
            'Sponsor Count', 'Image URL',
        ];

        // get all segments
        $segments = Segment::all();

        // update headers
        $headers = array_merge($headers, $segments->pluck('name')->toArray());

        // lets start output
        header('Content-type: text/csv');
        header('Content-type: text/plain');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="' . export_filename('sponsorship_records.csv') . '"');
        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, $headers);

        // loop over each sponsorship (chunk groups of 300 for memory)
        $sponsorships->orderBy('id')->chunk(300, function ($sponsorships) use ($segments, $outstream) {
            foreach ($sponsorships as $sponsorship) {
                $imageURL = media_thumbnail($sponsorship);

                // basic fields
                $row = [
                    $sponsorship->reference_number,
                    $sponsorship->first_name,
                    $sponsorship->last_name,
                    $sponsorship->gender,
                    toLocalFormat($sponsorship->birth_date, 'date:csv'),
                    $sponsorship->age,
                    toLocalFormat($sponsorship->enrollment_date, 'date:csv'),
                    $sponsorship->months_waiting,
                    $sponsorship->longitude,
                    $sponsorship->latitude,
                    toLocalFormat($sponsorship->created_at, 'csv'),
                    strip_tags($sponsorship->biography),
                    $sponsorship->private_notes,
                    $sponsorship->sponsor_count,
                    $imageURL,
                ];

                // loop over all custom segments
                foreach ($segments as $segment) {
                    if ($sponsorship->allSegments) {
                        $row[] = $sponsorship->segmentValue($segment);
                    } else {
                        $row[] = null;
                    }
                }

                // write it
                fputcsv($outstream, $row);
            }
        });

        fclose($outstream);
        exit;
    }

    public function destroy()
    {
        try {
            $sponsorship = Sponsorship::findWithPermission(request('id'));
            $sponsorship->delete();
            $this->flash->success('Sponsorship deleted successfully.');
        } catch (DomainException $e) {
            $this->flash->error($e->getMessage());
        }

        return redirect()->to('jpanel/sponsorship');
    }

    public function restore()
    {
        // permission
        $sponsorship = \Ds\Domain\Sponsorship\Models\Sponsorship::withTrashed()->findOrFail(request('id'));
        $sponsorship->userCanOrRedirect('edit');

        if ($sponsorship->trashed()) {
            $sponsorship->restore();
        }

        // return to list
        return redirect()->to('jpanel/sponsorship/edit?i=' . $sponsorship->id);
    }

    public function save(SponsorshipSaveFormRequest $request)
    {
        $is_new = false;

        // create record if it doesn't exist
        if (! $request->filled('id')) {
            $sponsorship = Sponsorship::newWithPermission();
            $sponsorship->save();

        // find record
        } else {
            $sponsorship = Sponsorship::findOrFail($request->id);
        }

        // save fields
        $sponsorship->reference_number = $request->reference_number;
        $sponsorship->first_name = $request->first_name;
        $sponsorship->last_name = $request->last_name;
        $sponsorship->private_notes = $request->private_notes;
        $sponsorship->biography = $request->biography;
        $sponsorship->birth_date = $request->birth_date;
        $sponsorship->gender = $request->gender;
        $sponsorship->longitude = $request->longitude;
        $sponsorship->latitude = $request->latitude;
        // $sponsorship->payment_option_group_id = $request->payment_option_group_id;
        $sponsorship->is_enabled = (int) $request->is_enabled == 1;
        $sponsorship->enrollment_date = $request->enrollment_date;

        // if dpo data can be passed it, check for it
        if (user()->can('admin.dpo')) {
            collect([
                'meta1',
                'meta2',
                'meta3',
                'meta4',
                'meta5',
                'meta6',
                'meta7',
                'meta8',
                'meta9',
                'meta10',
                'meta11',
                'meta12',
                'meta13',
                'meta14',
                'meta15',
                'meta16',
                'meta17',
                'meta18',
                'meta19',
                'meta20',
                'meta21',
                'meta22',
                'meta23',
            ])->each(function ($key) use ($request, $sponsorship) {
                $sponsorship->{$key} = $request->{$key};
            });
        }

        // 0 = not sponsored
        if ($request->sponsored_status == 0) {
            $sponsorship->is_sponsored = 0;
            $sponsorship->is_sponsored_auto = 0;
        // 1 - sponsored
        } elseif ($request->sponsored_status == 1) {
            $sponsorship->is_sponsored = 1;
            $sponsorship->is_sponsored_auto = 0;
        // 2 - auto
        } elseif ($request->sponsored_status == 2) {
            $sponsorship->is_sponsored_auto = 1;

            // if is_sponsored_auto is dirty (meaning it's being newly set to 1)
            // we need to reset is_sponsored (a count of all sponsorships including possible sub-sites)
            // for now, just say 0
            if ($sponsorship->isDirty('is_sponsored_auto')) {
                $sponsorship->is_sponsored = 0;
            }
        }

        // update sponsorship
        $sponsorship->save();

        // detach previous payment options
        $sponsorship->paymentOptionGroups()->detach();

        // attach new payment option
        if ($request->filled('payment_option_group_id')) {
            $sponsorship->paymentOptionGroups()->attach([$request->payment_option_group_id]);
        }

        // detach all segments
        $sponsorship->segments()->detach();

        // new segments
        $all_segments = Segment::pluck('type', 'id');
        $new_segments = [];

        // loop over all new segments
        foreach ((array) $request->segments as $segment_id => $value) {
            $segmentType = $all_segments[$segment_id] ?? 'text';

            // segment settings
            if ((is_array($value) && trim($value[0]) !== '') || (! is_array($value) && trim($value) !== '')) {
                $new_segments[$segment_id] = [
                    'value' => (is_string($value)) ? trim($value) : null,
                    'segment_item_id' => (is_array($value) && trim($value[0]) !== '') ? $value[0] : null,
                ];

                if ($segmentType === 'date') {
                    $new_segments[$segment_id]['value'] = fromDateFormat($new_segments[$segment_id]['value'], 'date');
                }
            }
        }

        // attach segments
        $sponsorship->segments()->attach($new_segments);

        /* save image */
        if ($media = \Ds\Models\Media::storeUpload('image', ['collection_name' => 'sponsorships'])) {
            $sponsorship->media_id = $media->id;
            $sponsorship->save();
        }

        // back to sponsorship screen
        $this->flash->success('Sponsorship saved successfully.');

        return redirect()->to('jpanel/sponsorship/' . $sponsorship->id);
    }

    /**
     * View a sponsorship record.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function view($sponsorship_id = null)
    {
        // HANDLE LEGACY REQUESTS
        // redirect to new sponsorship URL
        if (! isset($sponsorship_id) && request()->filled('i')) {
            return redirect()->to('/jpanel/sponsorship/' . request('i'));
        }

        // find sponsorship
        $sponsorship = Sponsorship::with('allSegments.items', 'sponsors.member')
            ->where('id', $sponsorship_id)
            ->withTrashed()
            ->firstOrFail();

        // permission
        $sponsorship->userCanOrRedirect('view');

        // show the view
        return $this->getView('sponsorships/view', [
            '__menu' => 'sponsorship.all',
            'pageTitle' => $sponsorship->full_name . ' (' . $sponsorship->reference_number . ')',
            'sponsorship' => $sponsorship,
            'payment_options' => PaymentOptionGroup::all(),
            'public_segments' => Segment::publicSegments()->with(['items' => function ($qry) {$qry->orderBy('name', 'asc'); }])->get(),
            'private_segments' => Segment::privateSegments()->with(['items' => function ($qry) {$qry->orderBy('name', 'asc'); }])->get(),
        ]);
    }

    /**
     * New sponsorship record.
     *
     * @return \Illuminate\View\View
     */
    public function add()
    {
        // find sponsorship
        $sponsorship = Sponsorship::newWithPermission();

        // show the view
        return $this->getView('sponsorships/view', [
            '__menu' => 'sponsorship.all',
            'sponsorship' => $sponsorship,
            'payment_options' => PaymentOptionGroup::all(),
            'public_segments' => Segment::publicSegments()->get(),
            'private_segments' => Segment::privateSegments()->get(),
        ]);
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     */
    private function _baseQueryWithFilters()
    {
        // base query
        $query = Sponsorship::query();

        // search
        if (request()->filled('search')) {
            $query->where(function ($query) {
                $query->whereRaw("concat(COALESCE(first_name, '') ,' ', COALESCE(last_name, '')) like ?", ['%' . request('search') . '%']);
                $query->orWhere('reference_number', 'like', '%' . request('search') . '%');
            });
        }

        // enrollment date
        if (request()->filled('enrollment_date_start')) {
            $query->where('enrollment_date', '>=', request('enrollment_date_start') . ' 00:00:00');
        }
        if (request()->filled('enrollment_date_end')) {
            $query->where('enrollment_date', '<=', request('enrollment_date_end') . ' 23:59:59');
        }

        // refernce number
        if (request()->filled('search_ref_num')) {
            $query->where('reference_number', 'like', '%' . request('search_ref_num') . '%');
        }

        // birth date
        if (request()->filled('birth_date_start') && request()->filled('birth_date_end')) {
            $query->whereBetween('birth_date', [
                request('birth_date_start') . ' 00:00:00',
                request('birth_date_end') . ' 23:59:59',
            ]);
        } elseif (request()->filled('birth_date_start')) {
            $query->where('birth_date', '>=', request('birth_date_start') . ' 00:00:00');
        } elseif (request()->filled('birth_date_end')) {
            $query->where('birth_date', '<=', request('birth_date_end') . ' 23:59:59');
        }

        // sponsor_count
        if (request()->filled('sponsor_count_start') && request()->filled('sponsor_count_end')) {
            $query->where('sponsor_count', '>=', request('sponsor_count_start'))
                ->where('sponsor_count', '<=', request('sponsor_count_end'));
        } elseif (request()->filled('sponsor_count_start')) {
            $query->where('sponsor_count', '>=', request('sponsor_count_start'));
        } elseif (request()->filled('sponsor_count_end')) {
            $query->where('sponsor_count', '<=', request('sponsor_count_end'));
        }

        // gender
        if (request()->filled('gender')) {
            $query->where('gender', request('gender'));
        }

        // is_sponsored
        if (request()->filled('is_sponsored')) {
            $query->where('is_sponsored', request('is_sponsored'));
        }

        // is_enabled
        if (request()->filled('is_enabled')) {
            $query->where('is_enabled', request('is_enabled'));
        }

        // payment_option_group_id
        if (request()->filled('payment_option_group_id')) {
            // not linked to any payment optoin group
            if (request()->input('payment_option_group_id') == '0') {
                $query->leftJoin('sponsorship_payment_option_groups', 'sponsorship_payment_option_groups.sponsorship_id', '=', 'sponsorship.id')
                    ->whereNull('sponsorship_payment_option_groups.sponsorship_id');

            // LINKED to a specific payment option group
            } else {
                $query->join('sponsorship_payment_option_groups', 'sponsorship_payment_option_groups.sponsorship_id', '=', 'sponsorship.id')
                    ->where('sponsorship_payment_option_groups.payment_option_group_id', '=', request()->input('payment_option_group_id'));
            }
        }

        // is_mature
        if (request()->filled('is_mature')) {
            $now = toUtcFormat('today', 'datetime');
            if (request('is_mature') == 1) {
                $query->where(DB::raw("FLOOR(DATEDIFF('$now', birth_date)/365)"), '>=', (int) sys_get('sponsorship_maturity_age'));
            } elseif (request('is_mature') == 0) {
                $query->where(DB::raw("FLOOR(DATEDIFF('$now', birth_date)/365)"), '<', (int) sys_get('sponsorship_maturity_age'));
            }
        }

        // segment filters
        if (request()->filled('segment_filters')) {
            $segments = Segment::pluck('type', 'id');

            // loop over each filter provided
            foreach (request('segment_filters') as $segment_id => $value) {
                $segmentType = $segments[$segment_id] ?? 'text';

                // skip if value is blank
                if (! is_array($value) && trim($value) == '') {
                    continue;
                }

                if (is_array($value) && trim($value[0]) == '') {
                    continue;
                }

                if ($segmentType === 'date') {
                    $dateTo = fromDateFormat($value[1] ?? null, 'date');
                    $dateFrom = fromDateFormat($value[0] ?? null, 'date');

                    if ($dateTo && $dateFrom) {
                        $query->whereRaw('exists (select sponsorship_id from ' . tbl(SponsorshipSegment::table()) . ' where ' . tbl(SponsorshipSegment::table()) . '.sponsorship_id = ' . tbl(Sponsorship::table()) . '.id and ' . tbl(SponsorshipSegment::table()) . '.segment_id = ? and ' . tbl(SponsorshipSegment::table()) . '.value between ? and ?)', [(int) $segment_id, $dateFrom, $dateTo]);
                    } elseif ($dateFrom) {
                        $query->whereRaw('exists (select sponsorship_id from ' . tbl(SponsorshipSegment::table()) . ' where ' . tbl(SponsorshipSegment::table()) . '.sponsorship_id = ' . tbl(Sponsorship::table()) . '.id and ' . tbl(SponsorshipSegment::table()) . '.segment_id = ? and ' . tbl(SponsorshipSegment::table()) . '.value = ?)', [(int) $segment_id, $dateFrom]);
                    }

                    // if its an array of values (assuming its an array of ints)
                } elseif (is_array($value)) {
                    $value = array_map('intval', $value);
                    $query->whereRaw('exists (select sponsorship_id from ' . tbl(SponsorshipSegment::table()) . ' where ' . tbl(SponsorshipSegment::table()) . '.sponsorship_id = ' . tbl(Sponsorship::table()) . '.id and ' . tbl(SponsorshipSegment::table()) . '.segment_item_id in (' . implode(',', $value) . '))');

                // string value
                } else {
                    $query->whereRaw('exists (select sponsorship_id from ' . tbl(SponsorshipSegment::table()) . ' where ' . tbl(SponsorshipSegment::table()) . '.sponsorship_id = ' . tbl(Sponsorship::table()) . '.id and ' . tbl(SponsorshipSegment::table()) . '.segment_id = ? and ' . tbl(SponsorshipSegment::table()) . '.value like ?)', [(int) $segment_id, '%' . trim($value) . '%']);
                }
            }
        }

        return $query;
    }
}
