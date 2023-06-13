<?php

namespace Ds\Http\Controllers;

use Ds\Domain\QuickStart\Events\QuickStartTaskAffected;
use Ds\Domain\QuickStart\Tasks\BrandingSetup;
use Ds\Models\Setting;
use Ds\Models\Theme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BucketController extends Controller
{
    public function destroy()
    {
        // check permission
        user()->canOrRedirect('template.edit');

        Setting::whereNull('theme_id')->where('id', request('id'))->delete();

        return redirect()->to('jpanel/design/customize');
    }

    public function index()
    {
        // check permission
        user()->canOrRedirect('customize.edit');

        $__menu = 'design.customize';

        pageSetup('Customize', 'jpanel');

        $categories = app('theme')->asset('config/settings_schema.json');
        $categories = collect(json_decode($categories->value));

        if (json_last_error()) {
            $this->flash->error('Problem with settings schema. ' . json_last_error_msg());
        }

        $categories->push((object) [
            'name' => 'Custom',
            'settings' => Setting::whereNull('theme_id')->get(),
        ]);
        $categories = $categories->reject(function ($item) {
            return empty($item->settings);
        });

        $categories->each(function ($category) {
            $category->settings = collect($category->settings)
                ->map(function ($item) {
                    return $item->name ? setting($item->name) : $item;
                });
        });

        $std_categories = $categories->reject(function ($item) {
            return in_array($item->name, ['Advanced', 'Custom', 'Basics']);
        })->pluck('name');
        $adv_categories = $categories->filter(function ($item) {
            return in_array($item->name, ['Advanced', 'Custom']);
        })->sortByDesc('name')->pluck('name');

        // pull out basics
        $basics = $categories->filter(function ($item) {
            return $item->name === 'Basics';
        })->first();
        $categories = $categories->reject(function ($item) {
            return $item->name === 'Basics';
        });

        $hasUnlockedTheme = Theme::whereNotIn('id', [1])->where('locked', false)->exists();

        // render view
        return $this->getView('buckets/index', compact('__menu', 'categories', 'std_categories', 'adv_categories', 'basics', 'hasUnlockedTheme'));
    }

    public function insert()
    {
        // check permission
        user()->canOrRedirect('template.edit');

        $setting = new Setting;
        $setting->name = Str::slug(request('name'), '_');
        $setting->label = request('label');
        $setting->type = request('type');
        $setting->info = request('info');
        $setting->category = request('category');
        $setting->save();

        return redirect()->to('jpanel/design/customize');
    }

    public function save()
    {
        // check permission
        user()->canOrRedirect('customize.edit');

        setting((array) request('settings'));

        // system settings
        sys_set(request('basics'));

        QuickStartTaskAffected::dispatch(BrandingSetup::initialize());

        if (request()->ajax()) {
            return response()->json(true);
        }

        $this->flash->success('Customizations saved successfully!');

        return redirect()->to('jpanel/design/customize');
    }

    public function update()
    {
        // check permission
        user()->canOrRedirect('template.edit');

        $setting = Setting::whereNull('theme_id')->where('id', request('id'))->first() ?? new Setting;
        $setting->name = Str::slug(request('name'), '_');
        $setting->label = request('label');
        $setting->type = request('type');
        $setting->info = request('info');
        $setting->category = request('category');
        $setting->save();

        return redirect()->to('jpanel/design/customize');
    }

    public function view()
    {
        // check permission
        user()->canOrRedirect('template.edit');

        $__menu = 'design.customize';

        if (request('name')) {
            $isNew = 0;
            $title = 'View/Edit Customization';
            $action = '/jpanel/design/customize/update';
            $setting = Setting::whereNull('theme_id')->where('name', request('name'))->firstOrFail();
        } else {
            $isNew = 1;
            $title = 'Add Customization';
            $action = '/jpanel/design/customize/insert';
            $setting = new Setting;
        }

        pageSetup($title, 'jpanel');

        // categories
        $categories = DB::select('SELECT DISTINCT category AS name FROM settings ORDER BY category');

        return $this->getView('buckets/view', compact(
            '__menu',
            'isNew',
            'title',
            'action',
            'setting',
            'categories',
        ));
    }
}
