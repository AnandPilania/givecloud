<?php

namespace Ds\Http\Controllers;

use Ds\Models\Category;
use Ds\Models\PostType;
use Ds\Services\FeedService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FeedController extends Controller
{
    /** @var \Ds\Services\FeedService */
    protected $feedService;

    public function __construct(FeedService $feedService)
    {
        parent::__construct();

        $this->feedService = $feedService;
    }

    public function destroy()
    {
        $post_type = \Ds\Models\PostType::findOrFail(request('id'));

        $post_type->userCanOrRedirect('edit');

        $post_type->posts()->delete();
        $post_type->delete();

        $this->flash->success('Feed deleted.');

        return redirect()->route('backend.feeds.index');
    }

    public function index(): View
    {
        user()->can('posttype.view');

        $__menu = 'admin.feeds';

        $postTypes = PostType::query()->withCount('posts')->get();

        return view('feeds/index', [
            'pageTitle' => 'Feeds',
            '__menu' => $__menu,
            'postTypes' => $postTypes,
        ]);
    }

    public function insert()
    {
        // check permission
        user()->can('posttype.add');

        // create
        $postType = new PostType;

        // update
        $this->_updatePostType($postType);

        $this->flash->success('Feed created.');

        return redirect()->to('jpanel/feeds/edit?i=' . $postType->id);
    }

    public function update(Request $request)
    {
        try {
            $postType = \Ds\Models\PostType::findOrFail($request->id);

            // make sure the user has permission
            $postType->userCanOrRedirect('edit');

            $this->_updatePostType($postType);
            $this->flash->success('Feed saved.');

            return redirect()->to('jpanel/feeds/edit?i=' . request('id'));
        } catch (ModelNotFoundException $e) {
            $this->flash->error("The feed you are trying to update doesn't exist.");

            return redirect()->route('backend.feeds.index');
        }
    }

    public function _updatePostType($postType)
    {
        $postType->name = request('name') ?: null;
        $postType->sysname = request('sysname') ?: null;
        $postType->rss_link = request('rss_link') ?: null;
        $postType->rss_copyright = request('rss_copyright') ?: null;
        $postType->rss_description = request('rss_description') ?: null;
        $postType->itunes_subtitle = request('itunes_subtitle') ?: null;
        $postType->itunes_author = request('itunes_author') ?: null;
        $postType->itunes_owner_name = request('itunes_owner_name') ?: null;
        $postType->itunes_owner_email = request('itunes_owner_email') ?: null;
        $postType->itunes_category = request('itunes_category') ?: null;
        $postType->url_slug = request('url_slug') ?? Str::slug(request('name'), '-');
        $postType->isitunes = request('isitunes') ?? 0;
        $postType->template_suffix = request()->input('template_suffix') ?: null;
        $postType->default_template_suffix = request()->input('default_template_suffix') ?: null;
        $postType->media_id = request()->input('media_id') ?: null;
        $postType->show_social_share_links = request()->input('show_social_share_links') ?: false;

        if ($metadata = request('metadata')) {
            $postType->metadata($metadata);
        }

        $postType->save();

        $categories = $this->feedService->getOrCreateCategories($postType->getKey(), Arr::wrap(request('categories')));

        // delete categories
        Category::whereAssignableType('post_type')
            ->where('assignable_id', $postType->id)
            ->whereNotIn('id', $categories)
            ->delete();

        // sequence
        foreach ($categories as $sequence => $category) {
            Category::where('id', $category)
                ->update(['sequence' => $sequence]);
        }
    }

    public function view()
    {
        if (request('i')) {
            $feed = \Ds\Models\PostType::findWithPermission(request()->nonArrayInput('i'));
            $title = $feed->name;
            $__menu = 'admin.feeds';
            $action = '/jpanel/feeds/update';
        } else {
            $feed = \Ds\Models\PostType::newWithPermission();
            $feed->sysname = 'blog';
            $title = 'Feed';
            $__menu = 'website.feed-' . request('i');
            $action = '/jpanel/feeds/insert';
        }

        pageSetup($title, 'jpanel');

        $schemas = app('theme')->getTemplateMetadata('post-type');

        return $this->getView('feeds/view', compact('feed', 'title', '__menu', 'action', 'schemas'));
    }
}
