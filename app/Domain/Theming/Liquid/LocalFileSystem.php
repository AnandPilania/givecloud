<?php

namespace Ds\Domain\Theming\Liquid;

use Liquid\Exception\NotFoundException;
use Liquid\Exception\ParseException;
use Liquid\FileSystem\Local;

class LocalFileSystem extends Local
{
    /** @var string */
    protected $rootPath;

    /**
     * Create an instance.
     *
     * @param string $root
     *
     * @throws \Liquid\Exception\NotFoundException
     */
    public function __construct($root)
    {
        $this->rootPath = $root;

        parent::__construct($root);
    }

    /**
     * Get a path including the root.
     *
     * @param string $path
     * @return string
     */
    public function getPath(string $path = '')
    {
        return rtrim($this->rootPath, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Resolves a given path to a full template file path, making sure it's valid.
     *
     * @param string $templatePath
     * @return string
     *
     * @throws \Liquid\Exception\ParseException
     * @throws \Liquid\Exception\NotFoundException
     */
    public function fullPath($templatePath)
    {
        if (empty($templatePath)) {
            throw new ParseException('Empty template name');
        }

        $fullPath = $this->rootPath . '/' . dirname($templatePath) . '/' . basename($templatePath) . '.liquid';
        $realFullPath = realpath($fullPath);

        if ($realFullPath === false) {
            throw new NotFoundException("File not found: $fullPath");
        }

        if (strpos($realFullPath, $this->rootPath) !== 0) {
            throw new NotFoundException("Illegal template path: $realFullPath not under {$this->rootPath}");
        }

        return $realFullPath;
    }
}
