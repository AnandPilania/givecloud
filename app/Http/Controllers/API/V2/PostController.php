<?php

namespace Ds\Http\Controllers\API\V2;

use Ds\Http\Queries\PostsQuery;
use Ds\Http\Resources\PostResource;
use Ds\Models\Post;
use Ds\Models\PostType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function index(PostType $postType): AnonymousResourceCollection
    {
        user()->canOrRedirect(['post']);

        $posts = (new PostsQuery($postType->allPosts()))->paginate();

        return PostResource::collection($posts);
    }

    public function show(PostType $postType, Post $post): PostResource
    {
        user()->canOrRedirect(['post']);

        $post = (new PostsQuery($post->query()))->first();

        return new PostResource($post);
    }
}
