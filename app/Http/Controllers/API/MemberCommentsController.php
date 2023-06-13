<?php

namespace Ds\Http\Controllers\API;

use Ds\Http\Queries\Filters\MatchAgainstQueryFilter;
use Ds\Http\Requests\MemberCommentsControllerFormRequest;
use Ds\Models\Member;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MemberCommentsController extends Controller
{
    public function index(Member $member): AnonymousResourceCollection
    {
        $comments = QueryBuilder::for($member->comments()->with(['createdBy']))
            ->allowedFilters([
                AllowedFilter::custom('body', new MatchAgainstQueryFilter('body')),
            ])->orderByDesc('created_at')
            ->tap(function (Builder $comments) {
                return $this->paginate($comments);
            });

        return JsonResource::collection($comments)->additional([
            'meta' => [
                'unfiltered' => $member->comments()->count(),
            ],
        ]);
    }

    public function store(Member $member, MemberCommentsControllerFormRequest $request): JsonResponse
    {
        $comment = $member->comment($request->body);

        return $comment
            ? JsonResource::make($comment->load(['createdBy']))->response()
            : response()->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Prevents overflow if current page is higher than lastPage
     */
    protected function paginate(Builder $comments): LengthAwarePaginator
    {
        $paginated = $comments->paginate();

        if ($paginated->currentPage() <= $paginated->lastPage()) {
            return $paginated;
        }

        return $comments->paginate(null, ['*'], 'page', $paginated->lastPage());
    }
}
