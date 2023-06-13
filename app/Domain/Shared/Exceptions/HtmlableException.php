<?php

namespace Ds\Domain\Shared\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Responsable;

class HtmlableException extends Exception implements Responsable
{
    /** @var \Illuminate\Contracts\Support\Htmlable */
    protected $htmlable;

    /** @var int */
    protected $statusCode;

    /**
     * Create an instance.
     *
     * @param \Illuminate\Contracts\Support\Htmlable $htmlable
     * @param int $statusCode
     */
    public function __construct(Htmlable $htmlable, $statusCode = 200)
    {
        $this->htmlable = $htmlable;
        $this->statusCode = $statusCode;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return response($this->htmlable, $this->statusCode);
    }
}
