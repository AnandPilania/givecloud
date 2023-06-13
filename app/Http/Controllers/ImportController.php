<?php

namespace Ds\Http\Controllers;

use Ds\Common\TemporaryFile;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Import;
use Ds\Models\Media;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Throwable;

class ImportController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth');
        $this->middleware('requires.superUser');
    }

    /**
     * View main import view
     */
    public function index()
    {
        // find an active import
        $active = Import::where('is_complete', 0)->supportFacing()->where('stage', '!=', 'aborted')->first();

        // if there is an active import, redirect to it
        // this forces each site to only process one import at a time
        if ($active) {
            return redirect()->to('/jpanel/import/' . $active->id);
        }

        return view('import.index', [
            'pageTitle' => 'Import',
            '__menu' => 'admin.import',
        ]);
    }

    /**
     * A historical record of all old imports.
     */
    public function history()
    {
        $imports = Import::query()
            ->whereIn('stage', ['aborted', 'done'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('import.history', [
            'pageTitle' => 'Import History',
            '__menu' => 'admin.import',
            'imports' => $imports,
        ]);
    }

    /**
     * Observe the progress of an import
     */
    public function monitor($id)
    {
        $import = Import::findOrFail($id);

        if (request()->ajax()) {
            return response()->json($import);
        }

        return view('import.monitor', [
            'pageTitle' => "Importing {$import->file_name}",
            '__menu' => 'admin.import',
            'import' => $import,
        ]);
    }

    /**
     * Redirect to signed temporary URL.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function downloadFile($id)
    {
        $import = Import::findOrFail($id);

        if ($import->file) {
            return redirect()->to($import->file->temporary_url);
        }

        abort(404);
    }

    /**
     * Review the import log.
     */
    public function importMessages($id)
    {
        $import = Import::find($id);

        return view('import.import_messages', [
            'pageTitle' => "Import {$id}",
            '__menu' => 'admin.import',
            'import' => $import,
        ]);
    }

    /**
     * Review the import log.
     */
    public function analysisMessages($id)
    {
        $import = Import::find($id);

        return view('import.analysis_messages', [
            'pageTitle' => "Import {$id}",
            '__menu' => 'admin.import',
            'import' => $import,
        ]);
    }

    /**
     * Abort this import.
     */
    public function abort($id)
    {
        $import = Import::findOrFail($id);
        $import->abort();

        $this->flash->success('Import was successfully aborted.');

        return redirect()->to('/jpanel/import/' . $import->id);
    }

    /**
     * Abort this import.
     */
    public function startImport($id)
    {
        $import = Import::findOrFail($id);
        $import->startImport();

        return redirect()->to('/jpanel/import/' . $import->id);
    }

    /**
     * Upload a file for processing
     */
    public function upload()
    {
        $file = request()->file('import_file');

        if (! in_array($file->guessClientExtension(), ['csv', 'xlsx']) || ! in_array($file->getClientMimeType(), ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
            $this->flash->error('The import file must be either a Microsoft Excel Open XML Format Spreadsheet (XLSX) or Comma-Separated Values (CSV) file.');

            return redirect()->to('/jpanel/import');
        }

        try {
            $media = Media::storeUpload('import_file', [
                'collection_name' => 'imports',
                'visibility' => 'private',
            ]);

            if (! $media) {
                throw new MessageException('Failed to upload to Givecloud.');
            }

            // work, work, work, work, work (rihanna)
            $import = new Import;
            $import->import_type = request('import_type');
            $import->setImportFile($media);
            $import->analysisMessage('Uploaded file. Waiting to analyze file...');
            $import->analyze();

            return redirect()->to('/jpanel/import/' . $import->id);
        } catch (Throwable $e) {
            $this->flash->error('There was a problem with the file provided.');

            return redirect()->to('/jpanel/import');
        }
    }

    /**
     * Download a sample template
     */
    public function template($type)
    {
        $klass = 'Ds\\Jobs\\Import\\' . Str::studly($type);

        if (! class_exists($klass)) {
            abort(404);
        }

        $headers = app($klass)->getColumnDefinitions();

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator('Givecloud Support')
            ->setLastModifiedBy('Givecloud Support')
            ->setTitle('Example Import File')
            ->setSubject('Example Import File')
            ->setDescription('Example Import File for Office 2007 XLSX, generated by Givecloud.')
            ->setKeywords('Givecloud')
            ->setCategory('Givecloud');

        $spreadsheet->removeSheetByIndex(0);

        $worksheet1 = new Worksheet($spreadsheet, 'Example Import File');
        $worksheet2 = new Worksheet($spreadsheet, 'Headers Help');

        $spreadsheet->addSheet($worksheet1);
        $spreadsheet->addSheet($worksheet2);

        $worksheet1->fromArray($headers->pluck('name')->toArray());

        // list out all headers and their hint in separate rows
        $rows = [['Header', 'Required', 'Description', 'Type']];

        foreach ($headers as $header) {
            if (is_array($header->validator)) {
                $required_val = in_array('required', $header->validator) ? 'Y' : null;
                $validator_arr = $header->validator;
            } else {
                $required_val = (strpos($header->validator, 'required') !== false) ? 'Y' : null;
                $validator_arr = explode('|', $header->validator);
            }

            $type_val = null;
            if (in_array('alpha', $validator_arr) !== false) {
                $type_val = 'Letters (no spaces)';
            } elseif (in_array('alpha_dash', $validator_arr) !== false) {
                $type_val = 'Letters, Numbers, Dashes (no spaces)';
            } elseif (in_array('alpha_num', $validator_arr) !== false) {
                $type_val = 'Letters, Numbers (no spaces)';
            } elseif (in_array('date', $validator_arr) !== false) {
                $type_val = 'Date (YYYY-MM-DD) or (YYYY-MM-DD HH:MM:SS)';
            } elseif (in_array('email', $validator_arr) !== false) {
                $type_val = 'Email';
            } elseif (in_array('integer', $validator_arr) !== false) {
                $type_val = 'Number (no formatting or decimals)';
            } elseif (in_array('ip', $validator_arr) !== false) {
                $type_val = 'IP Address';
            } elseif (in_array('numeric', $validator_arr) !== false) {
                $type_val = 'Decimal (no formatting)';
            }

            $rows[] = [
                $header->name,
                $required_val,
                $header->hint,
                $type_val,
            ];
        }

        $worksheet2->fromArray($rows);

        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'ffe1e1e1'],
            ],
            'borders' => [
                'left' => ['borderStyle' => Border::BORDER_NONE],
                'right' => ['borderStyle' => Border::BORDER_NONE],
                'top' => ['borderStyle' => Border::BORDER_NONE],
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM],
            ],
        ];

        $coord = $worksheet1->getHighestColumn();

        $worksheet1->getStyle("A1:{$coord}1")->applyFromArray($headerStyle);
        $worksheet2->getStyle('A1:D1')->applyFromArray($headerStyle);
        $worksheet2->getStyle('A:A')->getFont()->setBold(true);

        foreach (range(1, count($headers)) as $index) {
            $worksheet1->getColumnDimensionByColumn($index)->setAutoSize(true);
        }

        foreach (range(1, 4) as $index) {
            $worksheet2->getColumnDimensionByColumn($index)->setAutoSize(true);
        }

        $worksheet1->freezePaneByColumnAndRow(1, 2);
        $worksheet2->freezePaneByColumnAndRow(2, 2);

        $file = (new TemporaryFile)->setExtension('xlsx');

        $writer = new Xlsx($spreadsheet);
        $writer->save($file->getFilename());

        return BinaryFileResponse::create($file)
            ->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                sys_get('ds_account_name') . "_{$type}_template.xlsx"
            );
    }
}
