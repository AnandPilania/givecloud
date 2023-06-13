<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\GroupAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class GroupAccountController extends Controller
{
    public function modal($group_account_id = 'add')
    {
        if ($group_account_id == 'add') {
            $groupAccount = null;
            $account = \Ds\Models\Account::findOrFail(request()->nonArrayInput('account_id'));
            $relatedgroups = collect([]);
        } else {
            $groupAccount = GroupAccount::with('account', 'group', 'orderItem')->findOrFail($group_account_id);
            $account = $groupAccount->account;
            $relatedgroups = GroupAccount::where('account_id', $groupAccount->account_id)->where('group_id', $groupAccount->group_id)->where('id', '!=', $groupAccount->id)->get();
        }

        $all_groups = \Ds\Models\Membership::orderBy('name')->get();
        $sources = GroupAccount::getSources();
        $end_reasons = GroupAccount::getEndReasons();

        $this->setViewLayout(false);

        return $this->getView('group_accounts/modal', compact(
            'group_account_id',
            'groupAccount',
            'account',
            'relatedgroups',
            'all_groups',
            'sources',
            'end_reasons'
        ));
    }

    public function update()
    {
        try {
            $groupAccount = GroupAccount::find(request('group_account_id'));
            $groupAccount->start_date = (request('start_date')) ? \Carbon\Carbon::createFromFormat('M j, Y', request('start_date')) : null;
            $groupAccount->end_date = (request('end_date')) ? \Carbon\Carbon::createFromFormat('M j, Y', request('end_date')) : null;
            $groupAccount->source = request('source');
            $groupAccount->end_reason = request('end_reason');

            // only add group_id to the data array if
            // it exists in the request
            // (we don't want to save null)
            if (request('group_id')) {
                $groupAccount->group_id = request('group_id');
            }

            $groupAccount->update();

            $this->flash->success('Group/Membership updated.');
        } catch (\Exception $e) {
            $this->flash->error('Oops! ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function insert()
    {
        try {
            $account = \Ds\Models\Member::findOrFail(request('account_id'));

            $account->addGroup(request('group_id'), request('start_date'), request('source'));

            $this->flash->success('Group/Membership updated.');
        } catch (\Exception $e) {
            $this->flash->error('Oops! ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $groupAccountId = (int) $request->input('group_account_id');

        try {
            $group_account = GroupAccount::findOrFail($groupAccountId);

            if ($group_account->order_item_id) {
                throw new MessageException('You cannot remove a group/membership linked to an contribution. Try setting an end date or deleting the contribution.');
            }

            $group_account->delete();

            $this->flash->success('Group/Membership removed.');
        } catch (ModelNotFoundException $e) {
            $this->flash->error("Oops! The group #$groupAccountId does not exist.");
        } catch (Throwable $e) {
            $this->flash->error('Oops! ' . $e->getMessage());
        }

        return redirect()->back();
    }
}
