<?php

namespace Ds\Http\Controllers\API;

use Ds\Http\Controllers\Controller;
use Ds\Http\Requests\CommentUpdateFormRequest;
use Ds\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class CommentsController extends Controller
{
    public function update(Comment $comment, CommentUpdateFormRequest $request): JsonResponse
    {
        $comment->body = $request->body;

        return $comment->save()
            ? JsonResource::make($comment)->response()
            : response()->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function destroy(Comment $comment): Response
    {
        $comment->userCanOrRedirect(['member.add']);

        return response(
            null,
            $comment->delete()
            ? Response::HTTP_NO_CONTENT
            : Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
