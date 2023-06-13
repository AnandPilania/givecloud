<?php

namespace Ds\Domain\Commerce\Exceptions;

class RedirectException extends GatewayException
{
    /** @var string */
    protected $redirect;

    /**
     * Create an instance.
     *
     * @param string $message
     * @param int|string $code
     * @param string $redirect
     */
    public function __construct($message, $code, $redirect)
    {
        parent::__construct($message, 0);

        $this->code = $code;
        $this->redirect = $redirect;
    }

    /**
     * Get the response.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getRedirect()
    {
        return redirect()->to($this->redirect);
    }
}
