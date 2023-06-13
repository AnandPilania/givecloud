<?php

namespace Ds\Domain\Shared\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Responsable;

class PermissionException extends Exception implements Responsable
{
    /** @var array */
    protected $permissions;

    /** @var string */
    protected $redirectTo;

    /**
     * @param string|array $permissions
     * @param string $redirectTo
     */
    public function __construct($permissions, $redirectTo = '/jpanel/')
    {
        parent::__construct('Sorry! You do not have sufficient permissions. Please contact your Givecloud administrator.');

        $this->permissions = is_array($permissions) ? $permissions : [$permissions];
        $this->redirectTo = $redirectTo;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
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
            return response($this->getMessage(), 403);
        }

        app('flash')->error($this->getMessage());

        return redirect()->to($this->redirectTo);
    }
}
