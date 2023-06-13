<?php

namespace Ds\Illuminate\Http;

/** @mixin \Illuminate\Http\Response */
class ResponseMixin
{
    /**
     * Create a PDF Response.
     */
    public function pdf()
    {
        return function ($view = null, $filename = [], $data = null) {
            if (is_array($filename)) {
                $data = $filename;
                $filename = 'document.pdf';
            }

            $pdf = app('pdf')->setFilename($filename);

            if (is_array($view) || preg_match('/<\s?[^\>]*\/?\s?>/i', $view)) {
                $pdf->loadHtml($view);
            } elseif ($view) {
                $pdf->loadView($view, $data);
            }

            return $pdf;
        };
    }

    /**
     * Create a Protected PDF Response.
     */
    public function protectedPdf()
    {
        return function ($view, $filename = [], $data = null) {
            return $this->pdf($view, $filename, $data)->setProtected(true);
        };
    }

    /**
     * Create a PDF respnose using labels.
     */
    public function labelsPdf()
    {
        return function ($data = null) {
            return app('pdf')->loadView('labels', $data)
                ->setOptions([
                    'margin-top' => 0,
                    'margin-right' => 0,
                    'margin-bottom' => 0,
                    'margin-left' => 0,
                ]);
        };
    }
}
