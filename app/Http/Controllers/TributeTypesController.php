<?php

namespace Ds\Http\Controllers;

use Ds\Models\TributeType;
use Illuminate\Support\Facades\DB;

class TributeTypesController extends Controller
{
    /**
     * View tribute type list.
     */
    public function index()
    {
        // tribute types
        user()->canOrRedirect('tributetype');

        // return view
        return view('tribute_types.index', [
            'pageTitle' => 'Tribute Types',
            '__menu' => 'admin.advanced',
            'tributeTypes' => $this->_baseQueryWithFilters()->get(),
        ]);
    }

    /**
     * Add a tribute type.
     */
    public function add()
    {
        // tribute types
        $tributeType = TributeType::newWithPermission();
        $tributeType->sequence = TributeType::max('sequence') + 1;

        // return view
        return view('tribute_types.edit', [
            'pageTitle' => 'New Tribute Type',
            '__menu' => 'admin.advanced',
            'tributeType' => $tributeType,
        ]);
    }

    /**
     * Edit a tribute type.
     */
    public function edit($tribute_type_id)
    {
        // tribute types
        $tributeType = TributeType::findWithPermission($tribute_type_id);

        // return view
        return view('tribute_types.edit', [
            'pageTitle' => $tributeType->label,
            '__menu' => 'admin.advanced',
            'tributeType' => $tributeType,
        ]);
    }

    /**
     * Update a tribute type
     */
    public function update($tribute_type_id)
    {
        // get tribute type
        $tributeType = TributeType::findWithPermission($tribute_type_id);

        // update tribute type
        $tributeType = $this->_updateModelFromInput($tributeType, request()->input());

        // ajax response
        if (request()->ajax()) {
            return response()->json($tributeType);
        }

        // html response
        $this->flash->success($tributeType->label . ' saved successfully.');

        return redirect()->to('/jpanel/tribute_types/' . $tributeType->id . '/edit');
    }

    /**
     * Delete a tribute type
     */
    public function delete($tribute_type_id)
    {
        // get tribute type
        $tributeType = TributeType::findWithPermission($tribute_type_id);

        // delete tribute type
        $tributeType->delete();

        // ajax response
        if (request()->ajax()) {
            return response()->json($tributeType);
        }

        // html response
        $this->flash->success($tributeType->label . ' deleted successfully.');

        return redirect()->to('/jpanel/tribute_types');
    }

    /**
     * Update a tribute type
     */
    public function store()
    {
        // create tribute type
        $tributeType = TributeType::newWithPermission();

        // update tribute type
        $tributeType = $this->_updateModelFromInput($tributeType, request()->input());

        // ajax response
        if (request()->ajax()) {
            return response()->json($tributeType);
        }

        // html response
        $this->flash->success($tributeType->label . ' created successfully.');

        return redirect()->to('/jpanel/tribute_types/' . $tributeType->id . '/edit');
    }

    /**
     * Update a tribute type
     *
     * @param \Ds\Models\TributeType $tributeType
     * @param array $input
     * @return \Ds\Models\TributeType
     */
    private function _updateModelFromInput(TributeType $tributeType, $input)
    {
        // update tribute type
        $tributeType->is_enabled = ($input['is_enabled'] == 1);
        $tributeType->sequence = $input['sequence'];
        $tributeType->label = $input['label'];
        $tributeType->email_subject = $input['email_subject'];
        $tributeType->email_cc = $input['email_cc'];
        $tributeType->email_bcc = $input['email_bcc'];
        $tributeType->email_template = $input['email_template'];
        $tributeType->letter_template = $input['letter_template'];
        $tributeType->dp_id = $input['dp_id'];
        $tributeType->save();

        return $tributeType;
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     */
    private function _baseQueryWithFilters()
    {
        $tributeTypes = TributeType::query();

        // include stats
        $tributeTypes->join('tributes as x', 'x.tribute_type_id', '=', 'tribute_types.id', 'left')
            ->select('tribute_types.id', 'tribute_types.sequence', 'tribute_types.label', 'tribute_types.is_enabled', DB::raw('MIN(x.created_at) AS first_tribute_at'), DB::raw('MAX(x.created_at) AS last_tribute_at'), DB::raw('COUNT(DISTINCT x.id) as tribute_count'), DB::raw('AVG(x.amount) AS avg_amount'), DB::raw('SUM(x.amount) as total_amount'))
            ->groupBy('tribute_types.id', 'tribute_types.sequence', 'tribute_types.label', 'tribute_types.is_enabled')
            ->orderBy('tribute_types.sequence');

        /*$filters = (object)[];

        // search
        $filters->search = request("search");
        if ($filters->search) {
            $receipts->where(function($query) use ($filters)
            {
                $query->where(DB::raw("concat(first_name,' ',last_name)"), 'like', "%$filters->search%");
                $query->orWhere('address_01',                              'like', "%$filters->search%");
                $query->orWhere('address_02',                              'like', "%$filters->search%");
                $query->orWhere('zip',                                     'like', "%$filters->search%");
                $query->orWhere('phone',                                   'like', "%$filters->search%");
                $query->orWhere('email',                                   'like', "%$filters->search%");
                $query->orWhere('amount',                                  'like', "%$filters->search%");
                $query->orWhere('number',                                  'like', "%$filters->search%");
            });
        }

        // issued date
        $filters->issued_at_str = request("issued_at_str");
        $filters->issued_at_end = request("issued_at_end");
        if ($filters->issued_at_str && $filters->issued_at_end) {
            $receipts->whereBetween('issued_at', [
                $filters->issued_at_str . " 00:00:00",
                $filters->issued_at_end . " 23:59:59",
            ]);
        }
        else if ($filters->issued_at_str) {
            $receipts->where('issued_at', '>=', $filters->issued_at_str . " 00:00:00");
        }
        else if ($filters->issued_at_end) {
            $receipts->where('issued_at', '<=', $filters->issued_at_end . " 23:59:59");
        }*/

        return $tributeTypes;
    }
}
