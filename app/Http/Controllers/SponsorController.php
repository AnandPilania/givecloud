<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Shared\DataTable;
use Ds\Domain\Sponsorship\Models\Segment;
use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Domain\Sponsorship\Models\SponsorshipSegment;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Models\RecurringPaymentProfile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LiveControl\EloquentDataTable\ExpressionWithName;

class SponsorController extends Controller
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
     * Import sopnsors from orders.  Secret route.
     */
    public function sponsorsFromOrders()
    {
        Sponsor::createFromOrders();
    }

    /**
     * A list of sponsors
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // check permission
        user()->canOrRedirect('sponsor.view');

        $total_sponsorship_stats = (object) [
            'all' => Sponsorship::count(),
            'sponsored' => Sponsorship::where('is_sponsored', '=', '1')->count(),
            'local_sponsored' => Sponsor::count(),
            'not_sponsored' => Sponsorship::count() - Sponsorship::where('is_sponsored', '=', '1')->count(),
        ];

        $sponsorship_breakdown_stats = [
            [
                'label' => 'Sponsored',
                'value' => $total_sponsorship_stats->sponsored,
            ],
            [
                'label' => 'Unsponsored',
                'value' => $total_sponsorship_stats->not_sponsored,
            ],
        ];

        $total_sponsors_stats = (object) [
            'all' => Sponsor::count(),
            'active' => Sponsor::where('ended_at', '=', null)->count(),
            'ended' => Sponsor::where('ended_at', '!=', null)->count(),
        ];

        $sponsors_breakdown_stats = [
            [
                'label' => 'Active',
                'value' => $total_sponsors_stats->active,
            ],
            [
                'label' => 'Ended',
                'value' => $total_sponsors_stats->ended,
            ],
        ];

        $recurring_payments_stats = (object) [
            'suspended' => RecurringPaymentProfile::join('sponsors', 'sponsors.order_item_id', '=', 'recurring_payment_profiles.productorderitem_id', 'inner')->where('status', '=', RecurringPaymentProfileStatus::SUSPENDED)->count(),
            'active' => RecurringPaymentProfile::join('sponsors', 'sponsors.order_item_id', '=', 'recurring_payment_profiles.productorderitem_id', 'inner')->where('status', '=', RecurringPaymentProfileStatus::ACTIVE)->count(),
            'cancelled' => RecurringPaymentProfile::join('sponsors', 'sponsors.order_item_id', '=', 'recurring_payment_profiles.productorderitem_id', 'inner')->where('status', '=', RecurringPaymentProfileStatus::CANCELLED)->count(),
            'total' => RecurringPaymentProfile::join('sponsors', 'sponsors.order_item_id', '=', 'recurring_payment_profiles.productorderitem_id', 'inner')->where('status', '=', RecurringPaymentProfileStatus::ACTIVE)->sum('amt'),
        ];

        // $total = RecurringPaymentProfile::join('sponsors', 'sponsors.order_item_id', '=', 'recurring_payment_profiles.productorderitem_id', 'inner')->where('status','=',RecurringPaymentProfileStatus::ACTIVE)->sum('amt');

        // dd($total);

        // view for listing sponsors
        return $this->getView('sponsors/index', [
            '__menu' => 'sponsorship.sponsors',
            'pageTitle' => (sys_get('sponsorship_database_name')) ? 'Local Sponsors' : 'Sponsors',
            'sponsorship_breakdown_stats' => $sponsorship_breakdown_stats,
            'total_sponsorship_stats' => $total_sponsorship_stats,
            'total_sponsors_stats' => $total_sponsors_stats,
            'sponsors_breakdown_stats' => $sponsors_breakdown_stats,
            'recurring_payments_stats' => $recurring_payments_stats,
        ]);
    }

    /**
     * A list of sponsors ajax
     *
     * @return \Illuminate\Http\Response
     */
    public function index_ajax()
    {
        // permission
        user()->canOrRedirect('sponsorship');

        // generate data table
        $dataTable = new DataTable($this->_baseQueryWithFilters(), [
            new ExpressionWithName('sponsors.member_id', 'member_id'),
            new ExpressionWithName("concat(ifnull(member.last_name,'[none]'), ', ', ifnull(member.first_name,'[none]'))", '_member_full_name'),
            new ExpressionWithName("concat(ifnull(sponsorship.last_name,'[none]'), ', ', ifnull(sponsorship.first_name,'[none]'))", '_sponsorship_full_name'),
            new ExpressionWithName('sponsors.started_at', 'started_at'),
            new ExpressionWithName('sponsors.source', 'source'),
            new ExpressionWithName('recurring_payment_profiles.profile_id', '_profile_id'),
            new ExpressionWithName('sponsors.ended_at', 'ended_at'),
            new ExpressionWithName('sponsors.ended_reason', 'ended_reason'),

            // off the grid
            new ExpressionWithName('sponsors.sponsorship_id', 'sponsorship_id'),
            new ExpressionWithName('sponsors.order_item_id', 'order_item_id'),
            new ExpressionWithName('sponsors.id', 'id'),

            // new ExpressionWithName('SUM(CASE WHEN sponsors.ended_at IS NOT NULL THEN 0 ELSE 1 END)', 'sponsorships'),
            // new ExpressionWithName('SUM(CASE WHEN sponsors.ended_at IS NOT NULL THEN 1 ELSE 0 END)', 'ended_sponsorships'),
            // new ExpressionWithName('MIN(sponsors.started_at)', 'first_sponsorship_at'),
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($row) {
            return [
                dangerouslyUseHTML('<a class="ds-sponsor" data-sponsor-id="' . e($row->id) . '" href="#"><i class="fa fa-search"></i></a>'),
                dangerouslyUseHTML('<a href="' . route('backend.member.edit', $row->member_id) . '"><i class="fa fa-user"></i> ' . ($row->member->accountType && $row->member->accountType->is_organization) ? e($row->member->display_name) : e($row->_member_full_name) . '</a>'),
                dangerouslyUseHTML(((! $row->sponsorship->is_deleted) ? '<a href="/jpanel/sponsorship/' . e($row->sponsorship_id) . '">' : '') . ((! $row->sponsorship->gender) ? e($row->_sponsorship_full_name) : (($row->sponsorship->gender == 'F') ? '<div class="text-pink" style="display:inline;"><i class="fa fa-female"></i> ' . e($row->_sponsorship_full_name) . '</div>' : (($row->sponsorship->gender == 'M') ? '<div class="text-info" style="display:inline;"><i class="fa fa-male"></i> ' . e($row->_sponsorship_full_name) . '</div>' : ''))) . ((! $row->sponsorship->is_deleted) ? '</a>' : '') . (($row->sponsorship->is_deleted) ? '<span class="pull-right label label-xs label-danger">DELETED</span>' : '')),
                e(($row->started_at) ? $row->started_at->format('M j, Y') : ''),
                e($row->source),
                dangerouslyUseHTML('<a href="/jpanel/recurring_payments/' . e($row->_profile_id) . '">' . e($row->RecurringPaymentProfile->payment_string) . '</a>' . (($row->RecurringPaymentProfile->status == RecurringPaymentProfileStatus::CANCELLED) ? '<span class="pull-right label uppercase label-xs label-danger">' . e(RecurringPaymentProfileStatus::CANCELLED) . '</span>' : (($row->RecurringPaymentProfile->status == RecurringPaymentProfileStatus::SUSPENDED) ? '<span class="pull-right label uppercase label-xs label-warning">' . e(RecurringPaymentProfileStatus::SUSPENDED) . '</span>' : ''))),
                e(($row->ended_at) ? $row->ended_at->format('M j, Y') : ''),
                e($row->ended_reason),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    /**
     * A list of sponsors csv export
     */
    public function export()
    {
        // permission
        user()->canOrRedirect('sponsorship');

        // query
        $query = $this->_baseQueryWithFilters();
        $sponsors = $query->select('sponsors.*')
            ->with('recurringPaymentProfile');

        // build basic headers
        $headers = ['Sponsor First Name', 'Sponsor Last Name', 'Email', 'Address', 'Address 2', 'City', 'State', 'ZIP', 'Country', 'Phone', 'Recurring Amount', 'Recurring Day', 'Recurring Period', 'Sponsorship Reference', 'Sponsorship First Name', 'Sponsorship Last Name', 'Gender', 'Birth Date', 'Age', 'Started On', 'Source', 'Ended On', 'Ended Reason', '# of Payments'];

        // optionally include the DP ID
        if (dpo_is_enabled()) {
            $headers[] = 'DonorPerfect Donor ID';
        }

        // lets start output
        header('Content-type: text/csv');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="sponsors.csv"');
        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, $headers);

        // create chunks of 250 sponsors for easier processing on the server
        // (there are a bunch of relationships that are grabbed as well
        // which can take up a lot of memory)
        $sponsors->orderBy('id')->chunk(250, function ($chunk) use (&$outstream) {
            // loop over each sponsor within the chunk
            foreach ($chunk as $sponsor) {
                // basic fields
                $row = [
                    $sponsor->member->first_name,
                    $sponsor->member->last_name,
                    $sponsor->member->email,
                    $sponsor->member->bill_address_01,
                    $sponsor->member->bill_address_02,
                    $sponsor->member->bill_city,
                    $sponsor->member->bill_state,
                    $sponsor->member->bill_zip,
                    $sponsor->member->bill_country,
                    $sponsor->member->bill_phone,
                    ($sponsor->RecurringPaymentProfile) ? money($sponsor->RecurringPaymentProfile->amt, $sponsor->RecurringPaymentProfile->currency_code) : '',
                    ($sponsor->RecurringPaymentProfile) ? $sponsor->RecurringPaymentProfile->billing_period_day : '',
                    ($sponsor->RecurringPaymentProfile) ? $sponsor->RecurringPaymentProfile->billing_period_description : '',
                    $sponsor->sponsorship->reference_number,
                    $sponsor->sponsorship->first_name,
                    $sponsor->sponsorship->last_name,
                    $sponsor->sponsorship->gender,
                    toLocalFormat($sponsor->sponsorship->birth_date, 'date:csv'),
                    $sponsor->sponsorship->age,
                    toLocalFormat($sponsor->started_at, 'csv'),
                    $sponsor->source,
                    toLocalFormat($sponsor->ended_at, 'csv'),
                    $sponsor->ended_reason,
                    $sponsor->recurringPaymentProfile ? $sponsor->recurringPaymentProfile->transactions()->succeeded()->count() : 0,
                ];

                // optionally include the DP ID
                if (dpo_is_enabled()) {
                    $row[] = $sponsor->member->donor_id;
                }

                // write it
                fputcsv($outstream, $row);
            }
        });

        fclose($outstream);
        exit;
    }

    /**
     * A list of sponsors csv export
     */
    public function detailed_export()
    {
        // permission
        user()->canOrRedirect('sponsorship');

        // query
        $query = $this->_baseQueryWithFilters();
        $sponsors = $query->select('sponsors.*')
            ->with('RecurringPaymentProfile', 'sponsorship.allSegments.items');

        // build basic headers
        $headers = [
            'Sponsor First Name', 'Sponsor Last Name', 'Email', 'Address', 'Address 2',
            'City', 'State', 'ZIP', 'Country', 'Phone', 'Recurring Amount', 'Recurring Day',
            'Recurring Period', 'Sponsorship Reference', 'Sponsorship First Name', 'Sponsorship Last Name',
            'Gender', 'Birth Date', 'Age', 'Started On', 'Source', 'Ended On', 'Ended Reason', '# of Payments',
            'Reference', 'First Name', 'Last Name', 'Gender', 'Birth Date', 'Age', 'Enrollment Date',
            'Months Waiting', 'Longitude', 'Latitude', 'Created', 'Biography', 'Private Notes',
            'Sponsor Count', 'Image URL',
        ];

        // get all segments
        $segments = Segment::all();

        // update headers
        $headers = array_merge($headers, $segments->pluck('name')->toArray());

        // optionally include the DP ID
        if (dpo_is_enabled()) {
            array_unshift($headers, 'DonorPerfect Donor ID');
        }

        $filters = collect([
            'search' => request('search'),
            'sponsorship_start_from' => request('sponsorship_start_from'),
            'sponsorship_start_to' => request('sponsorship_start_to'),
            'sponsorship_ended_from' => request('sponsorship_ended_from'),
            'sponsorship_ended_to' => request('sponsorship_ended_to'),
            'source' => request('source'),
            'billing_period' => request('billing_period'),
            'sponsor_status' => request('sponsor_status'),
            'birth_date_start' => request('birth_date_start'),
            'birth_date_end' => request('birth_date_end'),
            'sponsor_count_start' => request('sponsor_count_start'),
            'sponsor_count_end' => request('sponsor_count_end'),
            'gender' => request('gender'),
            'is_sponsored' => request('is_sponsored'),
            'is_enabled' => request('is_enabled'),
            'payment_option_group_id' => request('payment_option_group_id'),
            'is_mature' => request('is_mature'),
        ])->reject(function ($value) {
            return empty($value);
        });

        if (request()->filled('segment_filters')) {
            foreach (request('segment_filters') as $segment_id => $value) {
                if (is_array($value)) {
                    $filters['s' . $segment_id] = implode(',', $value);
                } elseif ($value) {
                    $filters['s' . $segment_id] = $value;
                }
            }
        }

        $filters = $filters->map(function ($value, $key) {
            return Str::slug($key) . '[' . Str::slug($value) . ']';
        })->implode('_');

        $filename = strtolower(trim("sponsors--$filters", '-') . '.csv');

        // lets start output
        header('Content-type: text/plain');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, $headers);

        set_time_limit(300);

        // create chunks of 250 sponsors for easier processing on the server
        // (there are a bunch of relationships that are grabbed as well
        // which can take up a lot of memory)
        $sponsors->orderBy('id')->chunk(250, function ($chunk) use (&$outstream, $segments) {
            // loop over each sponsor within the chunk
            foreach ($chunk as $sponsor) {
                $sponsorship = $sponsor->sponsorship;

                // basic fields
                $row = [
                    $sponsor->member->first_name,
                    $sponsor->member->last_name,
                    $sponsor->member->email,
                    $sponsor->member->bill_address_01,
                    $sponsor->member->bill_address_02,
                    $sponsor->member->bill_city,
                    $sponsor->member->bill_state,
                    $sponsor->member->bill_zip,
                    $sponsor->member->bill_country,
                    $sponsor->member->bill_phone,
                    ($sponsor->RecurringPaymentProfile) ? (string) money($sponsor->RecurringPaymentProfile->amt, $sponsor->RecurringPaymentProfile->currency_code) : '',
                    ($sponsor->RecurringPaymentProfile) ? $sponsor->RecurringPaymentProfile->billing_period_day : '',
                    ($sponsor->RecurringPaymentProfile) ? $sponsor->RecurringPaymentProfile->billing_period_description : '',
                    $sponsor->sponsorship->reference_number,
                    $sponsor->sponsorship->first_name,
                    $sponsor->sponsorship->last_name,
                    $sponsor->sponsorship->gender,
                    toLocalFormat($sponsor->sponsorship->birth_date, 'date:csv'),
                    $sponsor->sponsorship->age,
                    toLocalFormat($sponsor->started_at, 'csv'),
                    $sponsor->source,
                    toLocalFormat($sponsor->ended_at, 'csv'),
                    $sponsor->ended_reason,
                    $sponsor->recurringPaymentProfile ? $sponsor->recurringPaymentProfile->transactions()->succeeded()->count() : 0,
                ];

                $imageURL = media_thumbnail($sponsorship);

                // basic sponsorship fields
                $row = array_merge($row, [
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
                ]);

                // loop over all custom segments
                foreach ($segments as $segment) {
                    if ($sponsorship->allSegments) {
                        $row[] = $sponsorship->segmentValue($segment);
                    } else {
                        $row[] = null;
                    }
                }

                // optionally include the DP ID
                if (dpo_is_enabled()) {
                    array_unshift($row, $sponsor->member->donor_id);
                }

                // write it
                fputcsv($outstream, $row);
            }
        });

        fclose($outstream);
        exit;
    }

    /**
     * View record
     */
    public function view($sponsor_id)
    {
        user()->canOrRedirect('sponsor.view');

        return view('sponsors.show', [
            'sponsor' => Sponsor::findWithPermission($sponsor_id),
        ]);
    }

    /**
     * Add record
     */
    public function add($sponsorship_id)
    {
        user()->canOrRedirect('sponsor.add');

        // find sponsor
        $sponsor = new Sponsor;
        $sponsor->started_at = toUtc();
        $sponsor->sponsorship_id = $sponsorship_id;
        $sponsor->sponsorship = Sponsorship::findWithPermission($sponsorship_id);

        // render view
        return view('sponsors.show', [
            'sponsor' => $sponsor,
        ]);
    }

    /**
     * Update record
     */
    public function update($sponsor_id)
    {
        // find sponsor
        $sponsor = Sponsor::findWithPermission($sponsor_id);

        // update sponsor
        $this->_updateFromInput($sponsor, request()->input());

        // despite the name of this controller method this is actually not an "update" method
        // it's an end sponor method so not having an ended_at will cause massive
        // data inconsistencies throughout
        if (empty($sponsor->ended_at)) {
            $sponsor->ended_at = now();
            $sponsor->ended_by = user('id');
            $sponsor->save();
        }

        event(new \Ds\Domain\Sponsorship\Events\SponsorWasEnded($sponsor, [
            'do_not_send_email' => ! request()->input('send_sponsor_end_email'),
        ]));

        // return json
        return response()->json($sponsor);
    }

    /**
     * Store record
     */
    public function store()
    {
        // find sponsor
        $sponsor = Sponsor::newWithPermission();

        // update sponsor
        $this->_updateFromInput($sponsor, request()->input());

        // get the full record
        $sponsor = Sponsor::find($sponsor->id);

        event(new \Ds\Domain\Sponsorship\Events\SponsorWasStarted($sponsor, [
            'do_not_send_email' => ! request()->input('send_sponsor_start_email'),
        ]));

        // render view
        return response()->json($sponsor);
    }

    /**
     * Delete record
     */
    public function destroy($sponsor_id)
    {
        // find sponsor w/ permission
        $sponsor = Sponsor::findWithPermission($sponsor_id, 'edit');
        $sponsor->delete();

        event(new \Ds\Domain\Sponsorship\Events\SponsorWasEnded($sponsor, [
            'do_not_send_email' => true,
        ]));

        // render view
        return response()->json(true);
    }

    /**
     * Private update function used for both updating and adding.
     */
    private function _updateFromInput($model, $input)
    {
        if (isset($input['sponsorship_id'])) {
            $model->sponsorship_id = (trim($input['sponsorship_id']) !== '') ? $input['sponsorship_id'] : null;
        }

        if (isset($input['member_id'])) {
            $model->member_id = (trim($input['member_id']) !== '') ? $input['member_id'] : null;
        }

        if (isset($input['started_at'])) {
            $model->started_at = (trim($input['started_at']) !== '') ? \Carbon\Carbon::createFromFormat('M d, Y', $input['started_at']) : \Carbon\Carbon::today();
        }

        if (isset($input['ended_at'])) {
            $model->ended_at = (trim($input['ended_at']) !== '') ? \Carbon\Carbon::createFromFormat('M d, Y', $input['ended_at']) : null;
        }

        if (isset($input['ended_at'])) {
            $model->ended_by = (trim($input['ended_at']) !== '') ? user('id') : null;
        }

        if (isset($input['source'])) {
            $model->source = (trim($input['source']) !== '') ? $input['source'] : null;
        }

        if (isset($input['ended_reason'])) {
            $model->ended_reason = (trim($input['ended_reason']) !== '') ? $input['ended_reason'] : null;
        }

        if (isset($input['ended_note'])) {
            $model->ended_note = (trim($input['ended_note']) !== '') ? $input['ended_note'] : null;
        }

        $model->save();
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     */
    private function _baseQueryWithFilters()
    {
        $query = \Ds\Domain\Sponsorship\Models\Sponsor::query()->with('member', 'sponsorship');

        $query->join('member', 'sponsors.member_id', '=', 'member.id', 'inner');
        $query->join(Sponsorship::table() . ' as sponsorship', 'sponsors.sponsorship_id', '=', 'sponsorship.id', 'left');
        $query->join('recurring_payment_profiles', 'sponsors.order_item_id', '=', 'recurring_payment_profiles.productorderitem_id', 'left');

        // search
        if (request()->filled('search')) {
            $query->where(function ($query) {
                $query->whereRaw("concat(member.first_name,' ', member.last_name) like ?", ['%' . request('search') . '%']);
                $query->orWhereRaw("concat(sponsorship.first_name,' ', sponsorship.last_name) like ?", ['%' . request('search') . '%']);
                $query->orWhereRaw('display_name like ?', ['%' . request('search') . '%']);
            });
        }

        // sponsorship started window
        if (request()->filled('sponsorship_start_from') && request()->filled('sponsorship_start_to')) {
            $query->whereBetween('started_at', [
                request('sponsorship_start_from') . ' 00:00:00',
                request('sponsorship_start_to') . ' 23:59:59',
            ]);
        } elseif (request()->filled('sponsorship_start_from')) {
            $query->where('started_at', '>=', request('sponsorship_start_from') . ' 00:00:00');
        } elseif (request()->filled('sponsorship_start_to')) {
            $query->where('started_at', '<=', request('sponsorship_start_to') . ' 23:59:59');
        }

        // sponsorship end window
        if (request()->filled('sponsorship_ended_from') && request()->filled('sponsorship_ended_to')) {
            $query->whereBetween('ended_at', [
                request('sponsorship_ended_from') . ' 00:00:00',
                request('sponsorship_ended_to') . ' 23:59:59',
            ]);
        } elseif (request()->filled('sponsorship_ended_from')) {
            $query->where('ended_at', '>=', request('sponsorship_ended_from') . ' 00:00:00');
        } elseif (request()->filled('sponsorship_ended_to')) {
            $query->where('ended_at', '<=', request('sponsorship_ended_to') . ' 23:59:59');
        }

        // sources
        if (request()->filled('source')) {
            $query->where('source', '=', request('source'));
        }

        // billing period
        if (request()->filled('billing_period')) {
            $query->where('billing_period', '=', request('billing_period'));
        }

        // sponsor status
        if (request()->filled('sponsor_status')) {
            if (request()->input('sponsor_status') == 'Active') {
                $query->where('ended_at', '=', null);
            } elseif (request()->input('sponsor_status') == 'Ended') {
                $query->where('ended_at', '!=', null);
            }
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

        // rpp status
        if (request()->filled('recurring_payments_status')) {
            $query->whereIn('recurring_payment_profiles.status', Arr::wrap(request('recurring_payments_status')));
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
            if (request('is_mature') == 1) {
                $query->where(DB::raw('FLOOR(DATEDIFF(NOW(), birth_date)/365)'), '>=', (int) sys_get('sponsorship_maturity_age'));
            } elseif (request('is_mature') == 0) {
                $query->where(DB::raw('FLOOR(DATEDIFF(NOW(), birth_date)/365)'), '<', (int) sys_get('sponsorship_maturity_age'));
            }
        }

        // segment filters
        if (request()->filled('segment_filters')) {
            // loop over each filter provided
            foreach (request('segment_filters') as $segment_id => $value) {
                // skip if value is blank
                if (! is_array($value) && trim($value) == '') {
                    continue;
                }

                // if its an array of values (assuming its an array of ints)
                if (is_array($value)) {
                    $query->whereRaw('exists (select sponsorship_id from ' . tbl(SponsorshipSegment::table()) . ' where ' . tbl(SponsorshipSegment::table()) . '.sponsorship_id = ' . tbl(Sponsorship::table()) . '.id and ' . tbl(SponsorshipSegment::table()) . '.segment_item_id in (?))', [implode(',', $value)]);

                // string value
                } else {
                    $query->whereRaw('exists (select sponsorship_id from ' . tbl(SponsorshipSegment::table()) . ' where ' . tbl(SponsorshipSegment::table()) . '.sponsorship_id = ' . tbl(Sponsorship::table()) . '.id and ' . tbl(SponsorshipSegment::table()) . '.segment_id = ? and ' . tbl(SponsorshipSegment::table()) . '.value like ?)', [(int) $segment_id, '%' . trim($value) . '%']);
                }
            }
        }

        return $query;
    }
}
