<?php

namespace Ds\Domain\Shared\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Responsable;

class RedirectException extends Exception implements Responsable
{
    /** @var string */
    protected $redirectTo;

    /** @var int */
    protected $statusCode;

    /**
     * Create an instance.
     *
     * @param string $redirectTo
     * @param int $statusCode
     */
    public function __construct($redirectTo, $statusCode = 302)
    {
        $this->redirectTo = $redirectTo;
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
        return redirect()->to($this->redirectTo, $this->statusCode);
    }
}
