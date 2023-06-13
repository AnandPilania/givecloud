<?php

namespace Ds\Common;

use Ds\Facades\Cli;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\ViewException;
use Knp\Snappy\Pdf as SnappyPdf;
use Swift_Attachment;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Throwable;

class Pdf implements Responsable
{
    /** @var string[] */
    protected $html = [];

    /** @var string */
    protected $filename = null;

    /** @var string */
    protected $password = null;

    /** @var array */
    protected $options = [];

    /** @var bool */
    protected $generated = false;

    /** @var \Ds\Common\TemporaryFile|null */
    protected $renderedFile;

    /** @var \Ds\Common\TemporaryFile|null */
    protected $protectedFile;

    /**
     * Get the filename.
     *
     * @return string|null
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set the filename.
     *
     * @param string $filename
     * @return $this
     */
    public function setFilename(string $filename): Pdf
    {
        $this->filename = $filename ?: null;

        return $this;
    }

    /**
     * Set the options.
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): Pdf
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set a password for the PDF.
     *
     * @param string|null $password
     * @return $this
     */
    public function setPassword(?string $password): Pdf
    {
        // by clearing just the protected we can reprotect
        // the generated PDF without having to regenerate
        if ($this->isProtected()) {
            $this->protectedFile = null;
        }

        $this->password = $password;

        return $this;
    }

    /**
     * Set a protection for the PDF.
     *
     * @param bool $protected
     * @return $this
     */
    public function setProtected(bool $protected): Pdf
    {
        $this->setPassword($protected ? Str::random(40) : null);

        return $this;
    }

    /**
     * Does the PDF have a password.
     *
     * @return bool
     */
    public function hasPassword(): bool
    {
        return (bool) $this->password;
    }

    /**
     * Has the PDF been generated yet.
     *
     * @return bool
     */
    protected function isGenerated(): bool
    {
        return $this->isRendered() && ($this->isProtected() || ! $this->hasPassword());
    }

    /**
     * Has the PDF been rendered yet.
     *
     * @return bool
     */
    protected function isRendered(): bool
    {
        return (bool) $this->renderedFile;
    }

    /**
     * Has the PDF been protected yet.
     *
     * @return bool
     */
    protected function isProtected(): bool
    {
        return (bool) $this->protectedFile;
    }

    /**
     * Load a HTML string.
     *
     * @param array|string|\Illuminate\Contracts\Support\Renderable $html
     */
    public function loadHtml($html)
    {
        $this->cleanGeneratedFiles();

        $html = Arr::wrap($html);

        foreach ($html as &$innerHtml) {
            if ($innerHtml instanceof Renderable) {
                $innerHtml = $innerHtml->render();
            }
        }

        $this->html = array_merge($this->html, $html);

        return $this;
    }

    /**
     * Load a View and convert to HTML.
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return $this
     */
    public function loadView($view, $data = [], $mergeData = [])
    {
        $this->cleanGeneratedFiles();

        return $this->loadHtml(
            View::make($view, $data, $mergeData)
        );
    }

    /**
     * Generate the PDF.
     */
    protected function generate($clean = false)
    {
        if ($clean) {
            $this->cleanGeneratedFiles();
        }

        if (! $this->isRendered()) {
            $this->renderHtml();
        }

        if ($this->hasPassword() && ! $this->isProtected()) {
            $this->applyProtection();
        }
    }

    /**
     * Render the HTML into a PDF.
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    protected function renderHtml()
    {
        $this->cleanGeneratedFiles();

        if (count($this->html) === 0) {
            throw new ViewException("Can't render a PDF without any HTML or Views.");
        }

        $inputs = collect();

        foreach ($this->html as $html) {
            $inputs[] = new TemporaryFile($html, 'html');
        }

        $this->renderedFile = (new TemporaryFile)->setExtension('pdf');

        try {
            app(SnappyPdf::class)->generate(
                $inputs->map->getFilename()->all(),
                $this->renderedFile,
                $this->options,
                true
            );
        } catch (Throwable $exception) {
            $this->renderedFile = null;
            throw $exception;
        }
    }

    /**
     * Apply protection (if enabled) to the file.
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    protected function applyProtection()
    {
        if (! $this->hasPassword()) {
            throw new FileException('A password is required to protect a PDF file.');
        }

        if (! $this->isRendered()) {
            $this->renderHtml();
        }

        $this->protectedFile = (new TemporaryFile)->setExtension('pdf');

        try {
            Cli::run([
                'qpdf',
                '--encrypt',
                '', // user-password
                (string) $this->password, // owner-password
                '128', // key-length
                '--print=full',
                '--modify=none',
                '--',
                $this->renderedFile->filename,
                $this->protectedFile->filename,
            ]);
        } catch (Throwable $exception) {
            $this->protectedFile = null;
            throw new FileException('There was a problem generating the PDF.');
        }
    }

    /**
     * Get the file.
     *
     * @return \Ds\Common\TemporaryFile
     */
    public function getFile(): TemporaryFile
    {
        if (! $this->isGenerated()) {
            $this->generate();
        }

        if ($this->hasPassword()) {
            return $this->protectedFile;
        }

        return $this->renderedFile;
    }

    /**
     * Save the PDF to a file.
     *
     * @param string $filename
     * @return $this
     */
    public function save(string $filename): Pdf
    {
        $this->getFile()->saveCopyAs($filename);

        return $this;
    }

    /**
     * Get the PDF as a string.
     *
     * @return string
     */
    public function getData(): string
    {
        return $this->getFile()->getContents();
    }

    /**
     * Render the PDF as a string.
     *
     * @return string
     */
    public function toDataUri(): string
    {
        return 'data:application/pdf;base64,' . base64_encode(
            $this->getData()
        );
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toResponse($request)
    {
        return $this->toInlineResponse();
    }

    /**
     * Return a response with the PDF as a data URI encoded.
     *
     * @return \Illuminate\Http\Response
     */
    public function toDataUriResponse(): Response
    {
        return new Response($this->toDataUri(), 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Make the PDF downloadable by the user.
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toDownloadResponse(string $filename = null): BinaryFileResponse
    {
        if (empty($filename)) {
            $filename = $this->getFilename() ?? 'document.pdf';
        }

        $filename = sanitize_filename(basename($filename));

        return BinaryFileResponse::create($this->getFile())
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
    }

    /**
     * Return a response with the PDF to show in the browser.
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toInlineResponse(string $filename = null): BinaryFileResponse
    {
        if (empty($filename)) {
            $filename = $this->getFilename() ?? 'document.pdf';
        }

        $filename = sanitize_filename(basename($filename));

        return BinaryFileResponse::create($this->getFile())
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
    }

    /**
     * Generate a Swift attachment using the PDF.
     *
     * @param string $filename
     * @return \Swift_Attachment
     */
    public function toSwiftAttachment(string $filename = null): Swift_Attachment
    {
        if (empty($filename)) {
            $filename = $this->getFilename() ?? 'document.pdf';
        }

        $filename = sanitize_filename(basename($filename));

        return new Swift_Attachment($this->getData(), $filename, 'application/pdf');
    }

    /**
     * Clean the generated files.
     */
    protected function cleanGeneratedFiles()
    {
        $this->renderedFile = null;
        $this->protectedFile = null;
    }

    /**
     * Return debug information for the PDF.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'filename' => $this->getFilename(),
            'has_password' => $this->hasPassword(),
            'html' => $this->html,
            'options' => $this->options,
            'generated' => $this->isGenerated(),
            'rendered' => $this->isRendered(),
            'protected' => $this->isProtected(),
        ];
    }

    /**
     * Get a string representation of the PDF.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getData();
    }
}
