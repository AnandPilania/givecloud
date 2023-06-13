<?php

namespace Ds\Http\Controllers;

use Ds\Models\Node;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function destroy()
    {
        // find the node we are deleting, with permission
        $node = \Ds\Models\Node::query()
            ->withoutRevisions()
            ->findWithPermission(request('id'), 'edit', '/jpanel/pages/edit?i=' . request('id'));

        // delete the node
        $node->delete();

        return redirect()->to('jpanel/pages');
    }

    public function index()
    {
        user()->canOrRedirect('node.view', '/jpanel');

        pageSetup('Pages & Menus', 'jpanel');

        return $this->getView('pages/index', [
            '__menu' => 'website.pages',
            'menus' => Node::menus()->orderBy('sequence')->get(),
            'categories' => $this->getCategories(),
            'templates' => Node::getTemplateSuffixes(),
        ]);
    }

    public function quickAdd()
    {
        $node = Node::newWithPermission();
        $node->isactive = request('is_enabled');
        $node->ishidden = 0;
        $node->type = request('type');
        $node->parentid = request('parent_id');
        $node->title = request('name');
        $node->url = request('url');
        $node->target = request('target');
        $node->template_suffix = request('template_suffix');
        $node->sequence = (Node::whereRaw('ifnull(parentid,0) = ?', [request('parent_id')])->max('sequence') ?? 0) + 1;
        $node->save();

        if ($node->type === 'html') {
            $node->url = $node->suggestServerFile(request('title'));
            $node->save();
        } elseif ($node->type === 'category') {
            $category = \Ds\Models\ProductCategory::with('childCategories')->where('id', request('category_id'))->first();
            $node->category_id = $category->id;
            $node->title = $category->name;
            $node->save();

            if (request('include_subcategories')) {
                \Ds\Repositories\MenuRepository::categoryToNode($category->childCategories, $node->id);
            }
        }

        if (request('redirect') == 'back') {
            return redirect()->back();
        }

        return redirect()->to('jpanel/pages/edit?i=' . $node->id);
    }

    public function insert()
    {
        $node = \Ds\Models\Node::newWithPermission();
        $node->isactive = request()->input('isactive') == 1;
        $node->title = request('title');
        $node->ishidden = request('ishidden');
        $node->type = request('type');
        $node->parentid = request('parentid');
        $node->sequence = (request()->input('sequence') == '') ? 9999 : request()->input('sequence'); // assume its the last
        $node->target = request('target');
        $node->requires_login = request('requires_login') == 1 ? 1 : 0;
        $node->hide_menu_link_when_logged_out = request('hide_menu_link_when_logged_out') == 1 ? 1 : 0;
        $node->category_id = is_numeric(request('category_id')) ? request('category_id') : null;

        if ($metadata = request('metadata')) {
            $node->metadata($metadata);
        }

        $node->save();

        if ($node->type === 'html') {
            $node->url = $node->suggestServerFile(request('title'));
            $node->save();
        }

        // how many sequences are there?
        $qSeq = db_query(sprintf('SELECT n2.id, n2.sequence FROM node n INNER JOIN node n2 ON n2.parentid = n.parentid WHERE n.id = %d ORDER BY n2.sequence', $node->id));

        // make sure sequences start from 1 and increment by 1 (fix weird sequence bug)
        $x = 1;
        while ($s = db_fetch_assoc($qSeq)) {
            $qUpdate = db_query(sprintf(
                'UPDATE node SET sequence = %d WHERE id = %d',
                $x,
                $s['id']
            ));
            $x = $x + 1;
        }

        return redirect()->to('jpanel/pages/edit?i=' . $node->id);
    }

    public function copy()
    {
        $node = \Ds\Models\Node::query()
            ->withoutRevisions()
            ->findWithPermission(request('id'), 'view', '/jpanel/pages/edit?i=' . request('id'));

        $new_node = $node->replicate();
        $new_node->title .= ' (COPY)';
        $new_node->isactive = false;
        $new_node->created_by = user('id');
        $new_node->created_at = \Carbon\Carbon::now();
        $new_node->updated_by = user('id');
        $new_node->updated_at = \Carbon\Carbon::now();

        if ($new_node->url) {
            $new_node->url .= '-copy';
        }

        $new_node->save();

        $this->flash->success("Copied {$node->name} successfully");

        return redirect()->to('jpanel/pages/edit?i=' . $new_node->id);
    }

    public function autosave(): JsonResponse
    {
        $node = Node::query()
            ->withoutRevisions()
            ->findWithPermission(request('id'));

        if ($node->supportsRevisions() && $node->requestHasChangesForRevisableContent()) {
            $node->createRevision(true);
        }

        return response()->json(['success' => true]);
    }

    public function update()
    {
        // find the node we are editting with permission
        $node = \Ds\Models\Node::query()
            ->withoutRevisions()
            ->findWithPermission(request('id'), 'edit', '/jpanel/pages/edit?i=' . request('id'));

        if ($node->supportsRevisions() && $node->requestHasChangesForRevisableContent()) {
            $node->createRevision();
        }

        pageSetup('', '');

        $node->fill(request()->only([
            'title',
            'parentid',
            'isactive',
            'ishidden',
            'type',
            'category_id',
            'url',
            'pagetitle',
            'template_suffix',
            'target',
            'requires_login',
            'hide_menu_link_when_logged_out',
            'body',
            'metadescription',
            'metakeywords',
            'featured_image_id',
            'alt_image_id',
        ]));

        if (request('type') === 'menu') {
            $node->body = null;
            $node->metadescription = null;
            $node->metakeywords = null;
            $node->featured_image_id = null;
            $node->alt_image_id = null;
        }

        if ($metadata = request('metadata')) {
            $node->metadata($metadata);
        }

        $node->save();

        // if the form sequence is different from the db sequence
        if ($node->sequence != request('sequence')) {
            // reorder pages
            $qUpdate = db_query(sprintf(
                'UPDATE node SET sequence = sequence+1 WHERE parentid = %d AND sequence >= %d',
                request('parentid'),
                request('sequence')
            ));

            // set sequence of this page
            $qUpdate = db_query(sprintf(
                'UPDATE node SET sequence = %d WHERE id = %d',
                request('sequence'),
                request('id')
            ));
        }

        // how many sequences are there?
        $qSeq = db_query(sprintf('SELECT n2.id, n2.sequence FROM node n INNER JOIN node n2 ON n2.parentid = n.parentid WHERE n.id = %d ORDER BY n2.sequence', request('id')));

        // make sure sequences start from 1 and increment by 1 (fix weird sequence bug)
        $x = 1;
        while ($s = db_fetch_assoc($qSeq)) {
            $qUpdate = db_query(sprintf(
                'UPDATE node SET sequence = %d WHERE id = %d',
                $x,
                $s['id']
            ));
            $x = $x + 1;
        }

        return redirect()->to("jpanel/pages/edit?i={$node->id}");
    }

    public function view()
    {
        user()->canOrRedirect('node.view', '/jpanel/pages');

        $__menu = 'website.pages';

        if (request('i')) {
            $node = \Ds\Models\Node::query()
                ->withoutRevisions()
                ->findWithPermission(request('i'), 'view', '/jpanel/pages');

            if ($node->supportsRevisions() && request('revision')) {
                $node->useRevision((int) request('revision'));
            }

            $title = $node->title;
            $action = '/jpanel/pages/update';
        } else {
            $node = \Ds\Models\Node::newWithPermission();
            $title = 'Add Web Page';
            $action = '/jpanel/pages/insert';
        }

        $isNew = ! $node->exists;

        pageSetup($title, 'jpanel');

        $qSequence = db_query(sprintf('SELECT n.sequence FROM node n WHERE n.parentid = %d ORDER BY n.sequence', $node->parentid));
        $qSection = db_query(sprintf('SELECT n.id, n.title, n.level FROM node n WHERE (n.parentid = 0 OR n.parentid IS NULL) AND n.id != %d and protected = 0 ORDER BY n.sequence', $node->id));

        /* membership ids required */
        $membership_ids_required = membership_access_get_by_parent('node', $node->id);
        $membership_list = [];
        if (count($membership_ids_required) > 0) {
            foreach ($membership_ids_required as $membership_id) {
                $membership_list[] = membership_get($membership_id);
            }
        }

        $schemas = app('theme')->getTemplateMetadata('page');
        $content_editor_classes = app('theme')->getContentEditorClasses($schemas);

        $tinymce_classes = [
            trim('template--page-' . $node->template_suffix, '-'),
            "page-{$node->id}",
        ];

        $categories = $this->getCategories();

        return $this->getView('pages/view', compact(
            '__menu',
            'node',
            'title',
            'action',
            'isNew',
            'qSequence',
            'qSection',
            'membership_ids_required',
            'membership_list',
            'schemas',
            'content_editor_classes',
            'tinymce_classes',
            'categories',
        ));
    }

    public function addAllCategories()
    {
        user()->canOrRedirect(['node.add'], '/jpanel/pages');

        // move all categories over to nodes
        \Ds\Repositories\MenuRepository::categoryToNode();

        $this->flash->success('Successfully added categories to your menu.');

        return redirect()->to('jpanel/pages');
    }

    private function getCategories()
    {
        $cats = DB::table('productcategory as c')
            ->selectRaw('c.id, c.name, c.url_name AS url')
            ->where(function ($query) {
                $query->whereNull('c.parent_id');
                $query->orWhere('c.parent_id', 0);
            })->orderBy('c.sequence')
            ->get();

        foreach ($cats as $cat) {
            $cat->categories = DB::table('productcategory as c')
                ->selectRaw('c.id, c.name, c.url_name AS url')
                ->where('c.parent_id', $cat->id)
                ->orderBy('c.sequence')
                ->get();
        }

        return $cats;
    }
}
