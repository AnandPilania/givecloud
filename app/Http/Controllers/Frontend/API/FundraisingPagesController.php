<?php

namespace Ds\Http\Controllers\Frontend\API;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\FundraisingPage;
use Ds\Models\Member as Account;

class FundraisingPagesController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member', ['except' => [
            'getPages',
            'createPage',
            'getPage',
        ]]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPages()
    {
        return $this->success();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPage()
    {
        return $this->success();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPage(FundraisingPage $fundraisingPage)
    {
        return $this->success([
            'fundraising_page' => Drop::factory($fundraisingPage, 'FundraisingPage'),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePage(FundraisingPage $fundraisingPage, Account $account)
    {
        if ($account->id !== $fundraisingPage->member_organizer_id) {
            return $this->failure('Access denied.', 403);
        }

        return $this->success([
            'fundraising_page' => Drop::factory($fundraisingPage, 'FundraisingPage'),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePage(FundraisingPage $fundraisingPage, Account $account)
    {
        if ($account->id !== $fundraisingPage->member_organizer_id) {
            return $this->failure(__('general.access_denied'), 403);
        }

        $fundraisingPage->delete();

        return $this->success();
    }
}
