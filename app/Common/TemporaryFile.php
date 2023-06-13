<?php

namespace Ds\Common;

use Illuminate\Support\Str;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * @property-read string $contents
 * @property-read string $extension
 * @property-read string $filename
 */
class TemporaryFile
{
    /** @var bool */
    protected $cleanup = true;

    /** @var string */
    protected $directory;

    /** @var bool */
    protected $exists = false;

    /** @var string */
    protected $extension;

    /** @var string */
    protected $name;

    /** @var string */
    protected $prefix;

    /**
     * Create an instance.
     *
     * @param mixed $data
     * @param string|null $prefix
     * @param string|null $directory
     */
    public function __construct($data = null, ?string $extension = null, ?string $prefix = null, ?string $directory = null)
    {
        $this->name = Str::random(40);

        $this->setExtension($extension);
        $this->setPrefix($prefix);
        $this->setDirectory($directory);

        if ($data !== null) {
            $this->setContents($data);
        }
    }

    /**
     * Clean up the temporary file.
     */
    public function __destruct()
    {
        if ($this->cleanup) {
            $this->cleanupFilesystem();
        }
    }

    /**
     * Get the contents of the temporary file.
     *
     * @return string
     */
    public function getContents(): string
    {
        $filename = $this->getFilename();

        if (is_file($filename)) {
            return @file_get_contents($filename);
        }

        return '';
    }

    /**
     * Set the contents of the temporary file.
     *
     * @param mixed $data
     * @param int $flags
     * @return $this
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException
     */
    public function setContents($data, int $flags = 0): TemporaryFile
    {
        if (! is_dir($this->directory)) {
            if (false === @mkdir($this->directory, 0777, true) && ! is_dir($this->directory)) {
                throw new CannotWriteFileException("Unable to create directory: {$this->directory}");
            }
        } elseif (! is_writable($this->directory)) {
            throw new CannotWriteFileException("Unable to write in directory: {$this->directory}");
        }

        $filename = $this->getFilename();

        $bytes = @file_put_contents($filename, $data, $flags);

        if ($bytes === false) {
            throw new CannotWriteFileException("Unable to write file: $filename");
        }

        $this->exists = true;

        return $this;
    }

    /**
     * Set the directory for the temporary file.
     *
     * @param string|null $extension
     * @return $this
     */
    public function setExtension(?string $extension = null): TemporaryFile
    {
        if ($this->exists) {
            throw new FileException("Extension can't be changed after the file has been created.");
        }

        if ($extension && preg_match('/[^a-z0-9]/i', $extension)) {
            throw new ExtensionFileException(
                "The extension [$extension] contains invalid characters. " .
                'An extension must only contain alphanumeric characters.'
            );
        }

        $this->extension = (string) $extension;

        return $this;
    }

    /**
     * Get the extention.
     *
     * @return string
     */
    public function getExtension(): string
    {
        return (string) $this->extension;
    }

    /**
     * Get the filename for the temporary file.
     *
     * @return string
     */
    public function getFilename(): string
    {
        $filename = $this->directory . DIRECTORY_SEPARATOR . $this->prefix . $this->name;

        if ($this->extension) {
            $filename = "$filename.{$this->extension}";
        }

        return $filename;
    }

    /**
     * Get the file info.
     *
     * @return \SplFileInfo
     */
    public function getFileInfo(): SplFileInfo
    {
        $this->exists = true;

        return new SplFileInfo($this->getFilename());
    }

    /**
     * Set the prefix for the temporary file.
     *
     * @param string|null $prefix
     * @return $this
     */
    public function setPrefix(?string $prefix = null): TemporaryFile
    {
        if ($this->exists) {
            throw new FileException("Prefix can't be changed after the file has been created.");
        }

        if (empty($prefix)) {
            $prefix = 'givecloud_';
        }

        if (preg_match('/[^a-z0-9_-]/i', $prefix)) {
            throw new FileException(
                "The prefix [$prefix] contains invalid characters. " .
                'A prefix must only contain alphanumeric characters, underscores or dashes.'
            );
        }

        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Set the directory for the temporary file.
     *
     * @param string|null $directory
     * @return $this
     */
    public function setDirectory(?string $directory = null): TemporaryFile
    {
        if ($this->exists) {
            throw new FileException("Directory can't be changed after the file has been created.");
        }

        if (empty($directory)) {
            $directory = sys_get_temp_dir();
        }

        $this->directory = $directory;

        return $this;
    }

    /**
     * Should the filesystem be automatically cleaned up.
     *
     * @param bool $cleanup
     * @return $this
     */
    public function setFilesystemCleanup(bool $cleanup = true): TemporaryFile
    {
        if ($cleanup !== null) {
            $this->cleanup = $cleanup;
        }

        return $this;
    }

    /**
     * Save a copy of the temporary file.
     *
     * @param string $filename
     */
    public function saveCopyAs(string $filename)
    {
        if (! $this->exists) {
            throw new FileException("Can't save a copy before the file has been created.");
        }

        $directory = dirname($filename);

        if (! is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true) && ! is_dir($directory)) {
                throw new CannotWriteFileException("Unable to create directory: $directory");
            }
        } elseif (! is_writable($directory)) {
            throw new CannotWriteFileException("Unable to write in directory: $directory");
        }

        if (! copy($this->getFilename(), $filename)) {
            throw new CannotWriteFileException("Unable to write file: $filename");
        }
    }

    /**
     * Remove temporary file from the filesystem.
     */
    public function cleanupFilesystem()
    {
        $filename = $this->getFilename();

        if ($this->exists && is_file($filename)) {
            unlink($filename);
        }
    }

    /**
     * Provide accessors.
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($name === 'contents') {
            return $this->getContents();
        }

        if ($name === 'extension') {
            return $this->getExtension();
        }

        if ($name === 'filename') {
            return $this->getFilename();
        }
    }

    /**
     * Return debug information for the temporary file.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'cleaup' => $this->cleanup,
            'filename' => $this->getFilename(),
        ];
    }

    /**
     * Get the filename for the temporary file.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFilename();
    }
}
