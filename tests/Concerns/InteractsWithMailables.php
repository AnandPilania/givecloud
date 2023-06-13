<?php

namespace Tests\Concerns;

use Illuminate\Contracts\Support\Renderable as Mailable;
use Illuminate\Support\Facades\File;

trait InteractsWithMailables
{
    private function assertMailablePreview(Mailable $mailable, ?string $suffix = ''): void
    {
        $directory = base_path('tests/_mailables');

        File::ensureDirectoryExists($directory);

        $filename = sprintf(
            "{$directory}/%s%s.html",
            str_replace('\\', '-', get_class($mailable)),
            $suffix ? "--{$suffix}" : ''
        );

        File::put($filename, $mailable->render());

        $this->assertTrue(true);
    }
}
