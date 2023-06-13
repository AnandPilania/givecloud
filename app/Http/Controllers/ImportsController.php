<?php

namespace Ds\Http\Controllers;

use Ds\Models\Import;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ImportsController extends Controller
{
    public function index(): View
    {
        $imports = Import::query()
            ->subscriberFacing()
            ->orderBy('created_at', 'desc')
            ->with(['file'])
            ->get();

        return view('imports.index', [
            'imports' => $imports,
        ]);
    }

    public function create(): View
    {
        return view('imports.create');
    }

    public function download($id): ?RedirectResponse
    {
        $import = Import::findOrFail($id);

        if ($import->file) {
            return redirect()->to($import->file->temporary_url);
        }

        abort(404);

        return null;
    }

    public function store(): RedirectResponse
    {
        $import = new Import;
        $import->import_type = request('import_type');
        $import->stage = 'draft';

        $import->field_mapping = collect();

        $import->save();

        return redirect()->route('backend.imports.show', $import);
    }
}
