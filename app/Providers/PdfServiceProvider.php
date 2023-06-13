<?php

namespace Ds\Providers;

use Ds\Common\Pdf;
use Illuminate\Support\ServiceProvider;
use Knp\Snappy\Pdf as SnappyPdf;

class PdfServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('pdf', Pdf::class);

        $this->app->bind(SnappyPdf::class, function ($app) {
            $snappy = new SnappyPdf(
                $app['config']->get('snappy.pdf.binary', '/usr/local/bin/wkhtmltopdf'),
                $app['config']->get('snappy.pdf.options', []),
                $app['config']->get('snappy.pdf.env', [])
            );
            if ($app['config']->get('snappy.pdf.timeout', false) !== false) {
                $snappy->setTimeout($app['config']->get('snappy.pdf.timeout'));
            }

            return $snappy;
        });
    }
}
