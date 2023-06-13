<?php

namespace Ds\Domain\Shared\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class ForbiddenActionException extends Exception implements DisclosableException, Responsable
{
    /** @var string */
    protected $redirectTo;

    public function __construct(
        string $message = 'Sorry! You are trying to perform a forbidden action.',
        string $redirectTo = null
    ) {
        parent::__construct($message);

        $this->redirectTo = $redirectTo ?: url()->previous();
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response($this->getMessage(), Response::HTTP_FORBIDDEN);
        }

        app('flash')->error($this->getMessage());

        return redirect()->to($this->redirectTo);
    }
}
