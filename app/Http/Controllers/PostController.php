<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Shared\DataTable;
use Ds\Models\Media;
use Ds\Models\Post;
use Throwable;

class PostController extends Controller
{
    public function destroy()
    {
        // make sure the user has permission
        \Ds\Models\Post::findOrFail(request('id'))->userCanOrRedirect('edit');

        try {
            db_query('DELETE FROM `post` WHERE id = %d', request('id'));
        } catch (Throwable $e) {
            if (request()->ajax()) {
                return response(['success' => false]);
            }
        }

        if (request()->ajax()) {
            return response(['success' => true]);
        }

        $this->flash->success('Post deleted successfully.');

        return redirect()->to('jpanel/feeds/posts?i=' . request('type_id'));
    }

    public function index()
    {
        user()->canOrRedirect(['post']);

        $feed = \Ds\Models\PostType::with('categories')->findOrFail(request()->nonArrayInput('i'));
        $posts = null;
        $__menu = 'website.feed-' . $feed->id;

        if ($feed->sysname === 'slide') {
            $posts = $feed->posts()->orderBy('sequence')->orderBy('postdatetime')->get();
        } elseif ($feed->sysname === 'snippet') {
            $posts = $feed->posts()->orderBy('name')->get();
        }

        pageSetup($feed->name, 'jpanel');

        return $this->getView('posts/index', compact('feed', 'posts', '__menu'));
    }

    /**
     * Used for datatable fetches.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_ajax()
    {
        user()->canOrRedirect(['post']);

        $posts = $this->_basePostsQueryWithFilters(request('feed_id'));

        // generate data table
        $dataTable = new DataTable($posts, [
            'id',
            'postdatetime',
            'name',
            'isenabled',
            'url_slug',
            'type',
        ]);

        $dataTable->setFormatRowFunction(function ($post) {
            $status = '';

            if ($post->isenabled == 0) {
                $status .= '<div class="label label-warning label-pill label-outline">DRAFT</div> ';
            }

            return [
                dangerouslyUseHTML('<input type="checkbox" class="slave" name="selectedids" value="' . e($post->id) . '" />'),
                e(toLocalFormat($post->postdatetime)),
                dangerouslyUseHTML('<div class="meta-pre"></div><div class="title"><a href="/jpanel/feeds/posts/edit?i=' . e($post->id) . '">' . e($post->name ?: '(blank)') . '</a> ' . dangerouslyUseHTML($status) . '</div>'),
                dangerouslyUseHTML('<div class="stat-val"><a href="' . e($post->absolute_url) . '">' . e($post->absolute_url) . '</a></div>'),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function _basePostsQueryWithFilters($feed_id)
    {
        $posts = \Ds\Models\Post::with('postType')->where('type', $feed_id);

        // search
        if (request('search_term')) {
            $keywords = array_map('trim', explode(' ', request('search_term')));
            foreach ($keywords as $keyword) {
                $posts->where(function ($query) use ($keyword) {
                    $query->where('name', 'LIKE', "%{$keyword}%");
                });
            }
        }

        // category
        if (request('category_id')) {
            $posts->whereHas('categories', function ($q) {
                $q->whereIn('category_id', request('category_id'));
            });
        }

        // dates
        $published_from = fromLocal(request('published_from'));
        $published_to = fromLocal(request('published_to'));
        if ($published_from && $published_to) {
            $posts->whereBetween('postdatetime', [
                toUtc($published_from->startOfDay()),
                toUtc($published_to->endOfDay()),
            ]);
        } elseif ($published_from) {
            $posts->where('postdatetime', '>=', toUtc($published_from->startOfDay()));
        } elseif ($published_to) {
            $posts->where('postdatetime', '<=', toUtc($published_to->endOfDay()));
        }

        return $posts;
    }

    public function insert()
    {
        // check permission
        user()->canOrRedirect('post.add');

        // create
        $post = new \Ds\Models\Post;
        $post->type = request()->input('type_id') ?: null;

        // update
        $this->_updatePost($post);

        $this->flash->success('Post created successfully.');

        return redirect()->to('jpanel/feeds/posts/edit?i=' . $post->id);
    }

    public function sequence()
    {
        if (! user()->can('post.add')) {
            return response(['success' => false]);
        }

        try {
            // array of ids
            $ids = explode(',', request('sequence'));

            // set each
            $sequence = 1;
            foreach ($ids as $id) {
                // save
                db_query(sprintf(
                    'UPDATE post
                        SET sequence = %d
                        WHERE id = %d',
                    db_real_escape_string($sequence),
                    db_real_escape_string($id)
                ));

                // increment
                $sequence++;
            }
        } catch (Throwable $e) {
            return response(['sucess' => false]);
        }

        return response(['sucess' => true]);
    }

    public function update()
    {
        // find post
        $post = \Ds\Models\Post::findOrFail(request('id'));

        // check permission
        $post->userCanOrRedirect('edit', '/jpanel');

        // update
        $this->_updatePost($post);

        $this->flash->success('Post saved successfully.');

        return redirect()->to('jpanel/feeds/posts/edit?i=' . $post->id);
    }

    public function _updatePost(&$post)
    {
        $post->url_slug = request('url_slug');
        $post->postdatetime = toUtc(request('postdatetime'));
        $post->isenabled = request('isenabled');
        $post->name = request('name');
        $post->description = request('description');
        $post->url = request('url');
        $post->location = request('location');
        $post->tags = request('tags');
        $post->body = request('body');
        $post->embedcode = request('embedcode');
        $post->fineprint = request('fineprint');
        $post->expirydatetime = toUtc(request('expirydatetime'));
        $post->sequence = request('sequence');
        $post->misc1 = request('misc1');
        $post->misc2 = request('misc2');
        $post->misc3 = request('misc3');
        $post->modifieddatetime = request('modifieddatetime');
        $post->modifiedbyuserid = request('modifiedbyuserid');
        $post->author = request('author');
        $post->length_formatted = request('length_formatted');
        $post->length_milliseconds = request('length_milliseconds');
        $post->media_id = request('media_id');
        $post->featured_image_id = Media::find(request('featured_image_id'))->id ?? null;
        $post->alt_image_id = request('alt_image_id');
        // $post->template_suffix     = request()->input('template_suffix') ?: null;

        if ($metadata = request('metadata')) {
            $post->metadata($metadata);
        }

        $post->save();

        // categories
        $post->categories()->sync(request('categories'));
    }

    public function view()
    {
        if (request('i')) {
            $post = \Ds\Models\Post::findWithPermission(request('i'));
            $title = $post->name;
            $action = '/jpanel/feeds/posts/update';
            if ($post->postType->sysname === 'snippet') {
                $title = 'Edit Snippet';
            }
        } else {
            $post = \Ds\Models\Post::newWithPermission();
            $post->type = request('p');
            $post->author = trim(user('firstname') . ' ' . user('lastname'));
            $title = 'Add Post';
            $action = '/jpanel/feeds/posts/insert';
            if ($post->postType->sysname === 'snippet') {
                $title = 'Add Snippet';
            }
        }
        $isNew = ! $post->exists;

        pageSetup($title, 'jpanel');

        request()->merge(['p' => $post->type]);

        $__menu = "website.feed-{$post->type}";

        if ($isNew) {
            $post->postdatetime = now();
        }

        switch ($post->postType->sysname) {
            case 'blog':  $view = 'posts/blog'; break;
            case 'slide': $view = 'posts/slide'; break;
            case 'snippet': $view = 'posts/snippet'; break;
            default:      $view = 'posts/view';
        }

        $schemas = app('theme')->getTemplateMetadata('post');
        $content_editor_classes = app('theme')->getContentEditorClasses($schemas);

        $tinymce_classes = [
            trim('template--post-' . ($post->postType->default_template_suffix ?? ''), '-'),
            "post-{$post->id}",
        ];

        return $this->getView($view, compact(
            'post',
            'title',
            'action',
            'isNew',
            '__menu',
            'schemas',
            'content_editor_classes',
            'tinymce_classes',
        ));
    }

    /**
     * Duplicate a post.
     *
     * @param int $post_id
     * @return Illuminate\Http\RedirectResponse
     */
    public function duplicate($post_id)
    {
        $post = Post::findOrFail($post_id);
        $clone = $post->replicate();
        $clone->url_slug .= '-copy';
        $clone->name = 'Copy of ' . $clone->name;
        $clone->metadata = $post->metadata->toArray();
        $clone->save();

        $clone->categories()->sync($post->categories);

        $this->flash->success('Post duplicated successfully.');

        // back
        return redirect()->to('jpanel/feeds/posts/edit?i=' . $clone->id);
    }
}
