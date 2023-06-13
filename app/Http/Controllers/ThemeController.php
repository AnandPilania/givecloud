<?php

namespace Ds\Http\Controllers;

use Carbon\Carbon;
use Ds\Common\TemporaryFile;
use Ds\Facades\Cli;
use Ds\Models\Asset;
use Ds\Models\Theme;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use ZipStream\Option\Archive as ArchiveOptions;
use ZipStream\ZipStream;

class ThemeController extends Controller
{
    /**
     * Current theme and theme market
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // check permission
        user()->canOrRedirect('customize.edit');

        pageSetup('Themes', 'jpanel');

        $themes = Theme::whereNotIn('id', [1])->orderBy('title', 'asc')->get();

        if ($themes->where('locked', false)->isEmpty()) {
            abort(404);
        }

        // render view
        return $this->getView('themes/index', [
            '__menu' => 'design.customize',
            'themes' => $themes,
        ]);
    }

    /**
     * Activate theme
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate(Theme $theme)
    {
        sys_set(['active_theme' => $theme->id]);

        Cache::tags('theming')->flush();

        return redirect()->to('jpanel/design');
    }

    /**
     * Lock theme
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function lock(Theme $theme)
    {
        $theme->locked = true;
        $theme->save();

        if ($theme->active) {
            Cache::tags('theming')->flush();
        }

        return redirect()->to('jpanel/design');
    }

    /**
     * Unlock theme
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unlock(Theme $theme)
    {
        // check permission
        user()->canOrRedirect('customize.edit');

        // remove any existing assets
        $theme->assets()->delete();

        // copy the assets from the repo and save them into the sites DB
        $themePath = base_path("resources/themes/{$theme->handle}/");

        collect(File::allFiles($themePath))
            ->map(function ($file) use ($themePath) {
                return substr($file->getPathname(), strlen($themePath));
            })->filter(function ($filename) {
                return preg_match('#(?:content/.*[.]liquid|(?:config|layout|scripts|sections|snippets|styles|templates)/)#', $filename) === 1;
            })->each(function ($filename) use ($themePath, $theme) {
                $asset = new Asset;
                $asset->theme_id = $theme->id;
                $asset->key = $filename;
                $asset->value = file_get_contents("$themePath/$filename");
                $asset->created_at = Carbon::createFromTimeStamp(filemtime("$themePath/$filename"));
                $asset->updated_at = $asset->created_at->copy();
                $asset->save();
            });

        // set theme to unlocked
        $theme->locked = false;
        $theme->save();

        return redirect()->to("jpanel/themes/{$theme->id}/editor");
    }

    /**
     * Show the theme editor
     *
     * @return \Illuminate\View\View
     */
    public function editor(Theme $theme)
    {
        user()->canOrRedirect('template.view');

        if ($theme->locked) {
            $this->flash->error('Your theme needs to be unlocked to access the advanced editor.');

            return redirect()->to('/jpanel/design');
        }

        pageSetup($theme->title, 'jpanel');

        $assets = $theme->assets()
            ->select('id', 'theme_id', 'key', 'content_type', 'public_url')
            ->where('locked', false)
            ->orderBy('key', 'asc')
            ->get()
            ->keyBy('id');

        return $this->getView('themes/editor', [
            'body_classes' => 'theme-editor-app',
            '__menu' => 'design.advanced',
            'theme' => $theme,
            'assets' => $assets,
        ]);
    }

    /**
     * Retrieve asset.
     *
     * @return \Ds\Models\Asset
     */
    public function getAsset(Theme $theme, Asset $asset)
    {
        return $asset;
    }

    /**
     * Retrieve asset.
     *
     * @return \Ds\Models\Asset
     */
    public function saveAsset(Theme $theme, Asset $asset)
    {
        $asset->value = request('value');
        $asset->save();

        return $asset;
    }

    /**
     * Download theme.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadTheme(Theme $theme)
    {
        $opt = new ArchiveOptions;
        $opt->setEnableZip64(false);

        $zip = new ZipStream(null, $opt);

        $headers = [
            'Content-Type' => $opt->getContentType(),
            'Content-Disposition' => $opt->getContentDisposition() . "; filename=theme-{$theme->id}.zip",
            'Pragma' => 'public',
            'Cache-Control' => 'public, must-revalidate',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Length' => null,
        ];

        return new StreamedResponse(function () use ($theme, $zip) {
            $themeAdditions = '';
            $themeModifications = '';

            foreach ($theme->assets->where('locked', false) as $asset) {
                if ($asset->created_by === 1) {
                    $themeModifications .= $this->getAssetDiff($asset);
                } else {
                    $themeAdditions .= $this->getAssetDiff($asset);
                }

                $zip->addFile($asset->key, (string) $asset->value);
            }

            $zip->addFile('theme-additions.patch', $themeAdditions);
            $zip->addFile('theme-modifications.patch', $themeModifications);
            $zip->finish();
        }, 200, $headers);
    }

    private function getAssetDiff(Asset $asset): string
    {
        $oldValueFile = new TemporaryFile($asset->locked_value);
        $newValueFile = new TemporaryFile($asset->value);

        try {
            $output = Cli::run(['diff', '-upNbB', $oldValueFile->getFilename(), $newValueFile->getFilename()]);
        } catch (ProcessFailedException $e) {
            $output = $e->getProcess()->getOutput();
        }

        return str_replace(
            [$oldValueFile->getFilename(), $newValueFile->getFilename()],
            ["a/{$asset->key}", "b/{$asset->key}"],
            $output
        );
    }

    /**
     * Download theme.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadLatestTheme(Theme $theme)
    {
        $opt = new ArchiveOptions;
        $opt->setEnableZip64(false);

        $zip = new ZipStream(null, $opt);

        $headers = [
            'Content-Type' => $opt->getContentType(),
            'Content-Disposition' => $opt->getContentDisposition() . "; filename=theme-{$theme->id}-latest.zip",
            'Pragma' => 'public',
            'Cache-Control' => 'public, must-revalidate',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Length' => null,
        ];

        return new StreamedResponse(function () use ($theme, $zip) {
            $themePath = base_path("resources/themes/{$theme->handle}/");

            collect(File::allFiles($themePath))
                ->map(function ($file) use ($themePath) {
                    return substr($file->getPathname(), strlen($themePath));
                })->reject(function ($filename) {
                    return $filename === 'README.md';
                })->each(function ($filename) use ($themePath, $zip) {
                    $zip->addFileFromPath($filename, "$themePath/$filename");
                });

            $zip->finish();
        }, 200, $headers);
    }
}
