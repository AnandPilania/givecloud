<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Shared\DataTable;
use Ds\Http\Controllers\Controller;
use Ds\Models\GroupAccountTimespan;
use Ds\Models\Membership;
use Illuminate\Database\Eloquent\Builder;
use LiveControl\EloquentDataTable\ExpressionWithName;

class MembersController extends Controller
{
    public function index()
    {
        return $this->getView('reports/members', [
            'pageTitle' => 'Members',
            'memberships' => Membership::all(),
            '__menu' => 'reports.memberships',
        ]);
    }

    public function export()
    {
        user()->canOrRedirect('reports');
        user()->canOrRedirect('member');

        $query = $this->queryWithRequestFilters();

        return response()->streamDownload(function () use ($query) {
            $fp = fopen('php://output', 'w');

            fputcsv($fp, [
                'Supporter Display Name', 'Supporter Email',
                'Group name', 'Start Date', 'End Date', 'Status',
            ], ',', '"');

            $query->chunk(250, function ($query_chunk) use ($fp) {
                foreach ($query_chunk as $row) {
                    fputcsv($fp, [
                        $row->account->display_name,
                        $row->account->email,
                        $row->group->name,
                        toLocalFormat($row->start_date, 'M j, Y '),
                        toLocalFormat($row->end_date, 'M j, Y '),
                        $row->is_expired ? 'Expired' : 'Active',
                    ], ',', '"');
                }
            });
            fclose($fp);
        }, 'members.csv', [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description' => 'File Transfer',
            'Content-type' => 'text/csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ]);
    }

    public function get(): array
    {
        user()->canOrRedirect('reports');
        user()->canOrRedirect('member');

        $memberships = $this->queryWithRequestFilters();

        $dataTable = new DataTable($memberships, [
            new ExpressionWithName('group_account_timespan.account_id', 'account_id'),
            new ExpressionWithName('member.display_name', 'display_name'),
            new ExpressionWithName('member.email', 'email'),
            new ExpressionWithName('membership.name', 'name'),
            new ExpressionWithName('group_account_timespan.start_date', 'start_date'),
            new ExpressionWithName('group_account_timespan.end_date', 'end_date'),
            new ExpressionWithName('group_account_timespan.end_date', 'status'),
        ]);

        $dataTable->setFormatRowFunction(function ($row) {
            $status = 'Active';

            if ($row->is_expired) {
                $status = 'Expired';
            }

            return [
                dangerouslyUseHTML('<a href="' . route('backend.member.edit', $row->account->id) . '"><i class="fa fa-search"></i></a>'),
                e($row->display_name),
                e($row->email),
                e($row->name),
                e(toLocalFormat($row->start_date)),
                e($row->end_date ? toLocalFormat($row->end_date) : 'Never expires'),
                e($status),
            ];
        });

        return $dataTable->make();
    }

    protected function queryWithRequestFilters(): Builder
    {
        $query = GroupAccountTimespan::query()
            ->leftJoin('member', 'group_account_timespan.account_id', 'member.id')
            ->leftJoin('membership', 'group_account_timespan.group_id', 'membership.id')
            ->whereNull('membership.deleted_at');

        $this->requestStatusFilter($query);
        $this->requestGroupFilter($query);
        $this->requestStartDateFilter($query);
        $this->requestEndDateFilter($query);
        $this->requestSearchFilter($query);

        return $query;
    }

    protected function requestStatusFilter(Builder $query): void
    {
        if (! request()->filled('status')) {
            return;
        }

        $query->when(request('status') === 'active', function (Builder $query) {
            $query->active();
        });

        $query->when(request('status') === 'expired', function (Builder $query) {
            $query->inactive();
        });

        $query->when(request('status') === 'expiring', function (Builder $query) {
            $query->active()
                ->where('end_date', '<=', fromLocal('+1month'));
        });

        $query->when(request('status') === 'recently_expired', function (Builder $query) {
            $query->inactive()
                ->where('end_date', '>=', fromLocal('-1month'));
        });
    }

    protected function requestGroupFilter(Builder $query): void
    {
        if (! request()->filled('group')) {
            return;
        }

        $query->where('group_id', request('group'));
    }

    protected function requestStartDateFilter(Builder $query): void
    {
        if (! request()->filled('startDateBefore') && ! request()->filled('startDateAfter')) {
            return;
        }

        $query->when(request()->filled('startDateBefore'), function (Builder $query) {
            $query->whereDate('start_date', '<=', request('startDateBefore'));
        });

        $query->when(request()->filled('startDateAfter'), function (Builder $query) {
            $query->whereDate('start_date', '>=', request('startDateAfter'));
        });
    }

    protected function requestEndDateFilter(Builder $query): void
    {
        if (! request()->filled('endDateBefore') && ! request()->filled('endDateAfter')) {
            return;
        }

        $query->when(request()->filled('endDateBefore'), function (Builder $query) {
            $query->whereDate('end_date', '<=', request('endDateBefore'));
        });

        $query->when(request()->filled('endDateAfter'), function (Builder $query) {
            $query->where(function ($query) {
                $query->whereDate('end_date', '>=', request('endDateAfter'))
                    ->orWhereNull('end_date');
            });
        });
    }

    protected function requestSearchFilter(Builder $query): void
    {
        if (! request()->filled('search')) {
            return;
        }

        $keywords = array_map('trim', explode(' ', request('search')));

        foreach ($keywords as $keyword) {
            $query->where('display_name', 'LIKE', "%{$keyword}%");
        }
    }
}
