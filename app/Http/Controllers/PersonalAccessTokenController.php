<?php

namespace Ds\Http\Controllers;

use Ds\Http\Requests\PersonalAccessTokenDestroyFormRequest;
use Ds\Http\Requests\PersonalAccessTokenStoreFormRequest;
use Ds\Models\Passport\Token;
use Ds\Services\PersonalAccessTokenService;
use Illuminate\Http\RedirectResponse;
use Throwable;

class PersonalAccessTokenController extends Controller
{
    /** @var \Ds\Services\PersonalAccessTokenService */
    private $personalAccessTokenService;

    public function __construct(PersonalAccessTokenService $personalAccessTokenService)
    {
        parent::__construct();

        $this->personalAccessTokenService = $personalAccessTokenService;
    }

    public function store(PersonalAccessTokenStoreFormRequest $request): RedirectResponse
    {
        try {
            $token = $this->personalAccessTokenService->create(user(), $request->name);

            $this->flash->success(
                'Personal Access Token created: '
                . "<div style=\"word-break: break-all;\">$token->accessToken</div>"
            );
        } catch (Throwable $e) {
            $this->flash->error($e->getMessage());
        }

        return redirect()->back();
    }

    public function destroy(PersonalAccessTokenDestroyFormRequest $request, Token $token): RedirectResponse
    {
        if ($this->personalAccessTokenService->revoke($token)) {
            $this->flash->success("Personal Access Token $token->name has been successfully revoked.");
        } else {
            $this->flash->error("An error occured while revoking $token->name Personal Access Token.");
        }

        return redirect()->back();
    }
}
