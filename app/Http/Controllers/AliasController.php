<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Alias;
use Illuminate\Support\Str;

class AliasController extends Controller
{
    /**
     * View redirect list
     */
    public function index()
    {
        // check permission
        user()->canOrRedirect('alias');

        // return view
        return $this->getView('aliases/index', [
            '__menu' => 'admin.alias',
            'aliases' => Alias::all(),
        ]);
    }

    /**
     * Creating a new alias.
     */
    public function add()
    {
        // check permission
        user()->canOrRedirect('alias.add');

        // return view
        return $this->getView('aliases/edit', [
            '__menu' => 'admin.alias',
            'alias' => new Alias(['status_code' => 302, 'type' => 'http']),
        ]);
    }

    /**
     * Edit an existing alias.
     */
    public function edit($alias_id)
    {
        // check permission
        $alias = Alias::findWithPermission($alias_id);

        // return view
        return $this->getView('aliases/edit', [
            '__menu' => 'admin.alias',
            'alias' => $alias,
        ]);
    }

    /**
     * Insert a new alias record
     */
    public function insert()
    {
        user()->can('alias.add');

        try {
            $alias = $this->_updateFromInput(new Alias);
            $alias->save();
        } catch (MessageException $e) {
            $this->flash->error($e->getMessage());

            return redirect()->back();
        }

        // return view
        $this->flash->success('Redirect saved successfully.');

        return redirect()->to('/jpanel/aliases');
    }

    /**
     * Insert a new alias record
     */
    public function update($alias_id)
    {
        try {
            $alias = $this->_updateFromInput(Alias::findWithPermission($alias_id, 'edit'));
            $alias->save();
        } catch (MessageException $e) {
            $this->flash->error($e->getMessage());

            return redirect()->back();
        }

        // return view
        $this->flash->success('Redirect saved successfully.');

        return redirect()->to('/jpanel/aliases');
    }

    /**
     * Destroy an alias record
     */
    public function destroy($alias_id)
    {
        // update record w/ permission
        $alias = Alias::findWithPermission($alias_id, 'edit');
        $alias->delete();

        // return view
        $this->flash->success('Redirect deleted successfully.');

        return redirect()->to('/jpanel/aliases');
    }

    /**
     * Update the model from the input params
     */
    protected function _updateFromInput($model)
    {
        $data = [
            'source' => request('source', ''),
            'alias' => request('alias', '/'),
            'type' => request('type'),
        ];

        $data['source'] = preg_replace('#^(/+|https?://)#', '', rawurldecode($data['source']));

        $validator = app('validator')->make($data, [
            'source' => 'nullable',
            'alias' => ['required', 'regex:#^(/$|/[^/]|https?://[^/])#i'],
            'type' => 'required|in:http_301,http_302,html',
        ], [
            'source.regex' => 'Source is improperly formatted.',
            'alias.required' => 'Destination is required.',
            'alias.regex' => 'Destination is improperly formatted.',
        ]);

        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }

        $model->source = $data['source'];
        $model->alias = $data['alias'];

        if (Str::startsWith($data['type'], 'http_')) {
            $model->status_code = Str::after($data['type'], 'http_');
            $model->type = 'http';
        } else {
            $model->status_code = null;
            $model->type = $data['type'];
        }

        if (empty($data['source']) && $data['alias'] === '/') {
            throw new MessageException("Bad destination, homepage can't be redirected to itself.");
        }

        if ($data['source'] === $data['alias']) {
            throw new MessageException('Bad destination, detected a redirect loop.');
        }

        return $model;
    }
}
