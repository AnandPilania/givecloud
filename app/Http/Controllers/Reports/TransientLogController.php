<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Shared\DataTable;
use Ds\Http\Controllers\Controller;
use Ds\Models\TransientLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class TransientLogController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware(): void
    {
        $this->middleware(['auth', 'requires.superUser']);
    }

    public function index(): View
    {
        return view('reports.transient-logs');
    }

    public function show(TransientLog $log): TransientLog
    {
        return $log->load('user');
    }

    public function get(): array
    {
        $dataTable = new DataTable($this->getTransientLogs(), [
            'id',
            'origin',
            'level',
            'request_id',
            'source',
            'message',
            'created_at',
        ]);

        return $dataTable->setFormatRowFunction(function ($log) {
            return [
                dangerouslyUseHTML(sprintf('<a href="#" data-log-id="%d"><i class="fa fa-search"></i></a>', e($log->id))),
                e($log->origin),
                e($log->level),
                dangerouslyUseHTML(sprintf('<code>%s</code>', e(substr($log->request_id, 0, 13)))),
                e($log->source),
                e($log->message),
                dangerouslyUseHTML(sprintf('<div title="%s">%s <small class="text-muted">%s</small></div>', e(toLocalFormat($log->created_at, 'r')), e(toLocalFormat($log->created_at)), e(toLocalFormat($log->created_at, 'g:iA')))),
            ];
        })->make();
    }

    private function getTransientLogs(): Builder
    {
        $query = TransientLog::query()
            ->orderByDesc('id');

        request()->whenFilled('search', function ($search) use ($query) {
            $query->where(function (Builder $query) use ($search) {
                // only check for the 13 character non-random portion of the ordered-uuid
                // as this is all that's displayed for the request_id in the frontend
                if (strlen($search) === 13) {
                    $query->orWhere('request_id', 'like', "{$search}%");
                }

                $query->orWhere('message', 'like', "%{$search}%");
                $query->orWhere('context', 'like', "%{$search}%");
            });
        });

        request()->whenFilled('gte_created', function ($created) use ($query) {
            $query->whereDate('created_at', '>=', toUtc($created));
        });

        request()->whenFilled('lte_created', function ($created) use ($query) {
            $query->whereDate('created_at', '<=', toUtc($created));
        });

        return $query;
    }
}
