<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Domain\Sponsorship\Models\Segment;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Domain\Sponsorship\Models\SponsorshipSegment;
use Illuminate\Support\Facades\DB;

class SponsorshipsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('requires.feature:sponsorship');
    }

    /**
     * Public sponsorship list.
     *
     * /sponsorships
     *
     * Paging
     * ?page=5
     * ?per_page=15
     *
     * @return string
     */
    public function index()
    {
        pageSetup(__('frontend/sponsorships.index.sponsorship'), 'productList', 4);

        // get all filterable segments
        $segments = Segment::publicSegments()->filterable()->with('items')->get();

        // find only the geo segments
        $geo_segments = $segments->filter(function ($segment) {
            return $segment->is_geographic;
        });

        // find filters
        $filters = request()->only([
            'name',
            'ref',
            'gender',
            'age',
            'birth_year',
            'birth_month',
            'sponsored',
            'fields',
        ]);

        // sponsorship query
        $sponsorship_list = Sponsorship::with('segments.items', 'paymentOptionGroups')->active();

        // SYSTEM SETTING - sequencing
        if (sys_get('sponsorship_default_sorting') == 'id_asc') {
            $sponsorship_list->orderBy('id');
        } elseif (sys_get('sponsorship_default_sorting') == 'id_desc') {
            $sponsorship_list->orderBy('id', 'desc');
        } elseif (sys_get('sponsorship_default_sorting') == 'ref_asc') {
            $sponsorship_list->orderBy('reference_number');
        } elseif (sys_get('sponsorship_default_sorting') == 'ref_desc') {
            $sponsorship_list->orderBy('reference_number', 'desc');
        } elseif (sys_get('sponsorship_default_sorting') == 'fname_asc') {
            $sponsorship_list->orderBy('first_name');
        } elseif (sys_get('sponsorship_default_sorting') == 'fname_desc') {
            $sponsorship_list->orderBy('first_name', 'desc');
        } elseif (sys_get('sponsorship_default_sorting') == 'lname_asc') {
            $sponsorship_list->orderBy('last_name');
        } elseif (sys_get('sponsorship_default_sorting') == 'lname_desc') {
            $sponsorship_list->orderBy('last_name', 'desc');
        } elseif (sys_get('sponsorship_default_sorting') == 'random') {
            $sponsorship_list->orderBy(DB::raw('RAND()'));
        }

        // gender
        if ($filters['name']) {
            $sponsorship_list->where('first_name', 'like', '%' . $filters['name'] . '%');
        }

        // gender
        if ($filters['ref']) {
            $sponsorship_list->where('reference_number', 'like', '%' . $filters['ref'] . '%');
        }

        // gender
        if ($filters['gender']) {
            $sponsorship_list->where('gender', $filters['gender']);
        }

        // age
        if ($filters['age']) {
            $sponsorship_list->whereRaw("(DATE_FORMAT(FROM_DAYS(DATEDIFF(?,birth_date)), '%Y')+0) = ?", [
                fromLocal('now')->format('Y-m-d'),
                $filters['age'],
            ]);
        }

        // birth year
        if ($filters['birth_year']) {
            $sponsorship_list->whereRaw('year(birth_date) = ?', [$filters['birth_year']]);
        }

        // birth month
        if ($filters['birth_month']) {
            $sponsorship_list->whereRaw('month(birth_date) = ?', [$filters['birth_month']]);
        }

        // sponsored
        if (sys_get('sponsorship_show_sponsored_on_web') == 0) {
            $sponsorship_list->where('is_sponsored', 0);
        } elseif ($filters['sponsored'] === '1' || $filters['sponsored'] === '0') {
            $sponsorship_list->where('is_sponsored', ($filters['sponsored'] == 1) ? 1 : 0);
        }

        // for each filter passed in
        if ($filters['fields']) {
            $segments = Segment::whereIn('id', array_keys($filters['fields']))->get();
            foreach ($segments as $segment) {
                $value = $filters['fields'][$segment->id];

                if ($segment->type === 'multi-select' || $segment->type === 'advanced-multi-select') {
                    $value = array_filter(is_array($value) ? $value : explode(',', $value), 'strlen');
                    if (count($value) === 0) {
                        continue;
                    }

                    // add to list of filters
                    $filters['fields'][$segment->id] = array_map('intval', $value);

                    // if its an array of values (assuming its an array of ints)
                    $sponsorship_list->whereRaw('exists (select sponsorship_id from ' . tbl(SponsorshipSegment::table()) . ' where ' . tbl(SponsorshipSegment::table()) . '.sponsorship_id = ' . tbl(Sponsorship::table()) . '.id and ' . tbl(SponsorshipSegment::table()) . '.segment_item_id in (' . implode(',', $filters['fields'][$segment->id]) . '))');

                // string value
                } else {
                    $filters['fields'][$segment->id] = $value;

                    $sponsorship_list->whereRaw('exists (select sponsorship_id from ' . tbl(SponsorshipSegment::table()) . ' where ' . tbl(SponsorshipSegment::table()) . '.sponsorship_id = ' . tbl(Sponsorship::table()) . '.id and ' . tbl(SponsorshipSegment::table()) . '.segment_id = ? and ' . tbl(SponsorshipSegment::table()) . '.value like ?)', [$segment->id, '%' . trim($value) . '%']);
                }
            }
        }

        // get sponsorships
        $sponsorships = $sponsorship_list->paginate(36);

        // render template
        return $this->renderTemplate('sponsorships', [
            'filter' => $filters,
            'custom_fields' => $segments,
            'custom_geo_fields' => $geo_segments,
            'sponsorships' => $sponsorships->all(),
            'pagination' => get_pagination_data($sponsorships, $filters),
        ]);
    }

    /**
     * Public sponsorship details page.
     *
     * /sponsorship/{id}
     *
     * @return string
     */
    public function show($id)
    {
        // get the sponsorship being searched for
        $sponsorship = Sponsorship::with('segments.items')->active()->find($id);

        // if no sponsorship found OR the child is sponsored
        if (! $sponsorship || ($sponsorship->is_sponsored && sys_get('sponsorship_show_sponsored_on_web') == 0)) {
            if (! $sponsorship || ! sys_get('sponsorship_show_sponsored_on_details_page')) {
                return redirect()->to('/sponsorship');
            }
        }

        // page setup
        $pageTitle = $sponsorship->first_name;

        pageSetup($pageTitle, 'productList', 4);

        // render template
        return $this->renderTemplate('sponsorship', [
            'sponsorship' => $sponsorship,
            'payment_options_form' => '',
        ]);
    }
}
