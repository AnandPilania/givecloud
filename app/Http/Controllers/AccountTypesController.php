<?php

namespace Ds\Http\Controllers;

use Ds\Models\AccountType;

class AccountTypesController extends Controller
{
    /**
     * Add an account type.
     */
    public function add()
    {
        // account types
        $accountType = AccountType::newWithPermission();

        // return view
        return view('account_types.edit', [
            'pageTitle' => 'New Supporter Type',
            '__menu' => 'admin.advanced',
            'accountType' => $accountType,
        ]);
    }

    /**
     * Edit a account type.
     */
    public function edit($account_type_id)
    {
        // account types
        $accountType = AccountType::findWithPermission($account_type_id);

        // return view
        return view('account_types.edit', [
            'pageTitle' => $accountType->name,
            '__menu' => 'admin.advanced',
            'accountType' => $accountType,
        ]);
    }

    /**
     * Update a account type
     */
    public function update($account_type_id)
    {
        // get account type
        $accountType = AccountType::findWithPermission($account_type_id);

        // update account type
        $accountType = $this->_updateModelFromInput($accountType, request()->input());

        // ajax response
        if (request()->ajax()) {
            return response()->json($accountType);
        }

        // html response
        $this->flash->success($accountType->name . ' saved successfully.');

        return redirect()->route('backend.supporter_types.edit', $accountType->getKey());
    }

    /**
     * Delete an account type
     */
    public function destroy($account_type_id)
    {
        // get account type
        $accountType = AccountType::findWithPermission($account_type_id);

        // delete tribute type
        if (! $accountType->is_protected) {
            $accountType->delete();
        }

        // ajax response
        if (request()->ajax()) {
            return response()->json($accountType);
        }

        // html response
        $this->flash->success($accountType->name . ' deleted successfully.');

        return redirect()->route('backend.settings.supporters');
    }

    /**
     * Update an account type
     */
    public function store()
    {
        // create an account type
        $accountType = AccountType::newWithPermission();

        // update account type
        $accountType = $this->_updateModelFromInput($accountType, request()->input());

        // ajax response
        if (request()->ajax()) {
            return response()->json($accountType);
        }

        // html response
        $this->flash->success($accountType->name . ' created successfully.');

        return redirect()->route('backend.supporter_types.edit', $accountType->getKey());
    }

    /**
     * Update an account type
     */
    private function _updateModelFromInput(AccountType $accountType, $input)
    {
        // update account type
        $accountType->name = $input['name'];
        $accountType->sequence = $input['sequence'] ?? AccountType::max('sequence') ?? 0;
        $accountType->dp_code = $input['dp_code'];
        $accountType->on_web = $input['on_web'];

        // need to make sure that no other account types
        // are set to default before setting a new
        // default account type
        if ($input['is_default']) {
            AccountType::where('is_default', '=', '1')->update(['is_default' => 0]);
            $accountType->is_default = 1;
        }

        if (! $accountType->is_protected) {
            $accountType->is_organization = ($input['is_organization'] == 1);
        }

        $accountType->save();

        return $accountType;
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     */
    private function _baseQueryWithFilters()
    {
        return AccountType::query();
    }
}
