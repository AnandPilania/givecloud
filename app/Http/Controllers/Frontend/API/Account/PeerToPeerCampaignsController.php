<?php

namespace Ds\Http\Controllers\Frontend\API\Account;

use Ds\Enums\FundraisingPageType;
use Ds\Http\Controllers\Frontend\API\Controller;
use Ds\Http\Requests\Frontend\API\Account\PeerToPeerFundraisingPageStoreFormRequest;
use Ds\Http\Resources\PeerToPeer\FundraisingPageResource;
use Ds\Models\FundraisingPage;
use Ds\Models\Member;
use Ds\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeerToPeerCampaignsController extends Controller
{
    protected function registerMiddleware(): void
    {
        $this->middleware('auth.member');
    }

    public function create(PeerToPeerFundraisingPageStoreFormRequest $request, Member $supporter): JsonResponse
    {
        $product = Product::donationForms()
            ->hashid($request->fundraising_form_id)
            ->firstOrFail();

        $fundraisingPage = new FundraisingPage;
        $fundraisingPage->status = 'active';
        $fundraisingPage->product_id = $product->id;
        $fundraisingPage->member_organizer_id = $supporter->id;
        $fundraisingPage->type = FundraisingPageType::STANDALONE;
        $fundraisingPage->currency_code = $request->currency_code ?? currency()->getCode();
        $fundraisingPage->title = $request->title;
        $fundraisingPage->goal_amount = numeral($request->goal_amount)->toFloat();
        $fundraisingPage->is_team = $request->fundraiser_type === 'team';
        $fundraisingPage->team_name = $fundraisingPage->is_team ? $request->team_name : null;
        $fundraisingPage->avatar_name = $request->avatar_name;
        $fundraisingPage->activated_date = now();
        $fundraisingPage->save();

        return $this->success(['fundraising_page' => FundraisingPageResource::make($fundraisingPage)]);
    }

    public function get(string $hashcode, Member $supporter): JsonResponse
    {
        $fundraisingPage = $supporter->fundraisingPages()
            ->standaloneType()
            ->hashid($hashcode)
            ->firstOrFail();

        return $this->success(['fundraising_page' => FundraisingPageResource::make($fundraisingPage)]);
    }

    public function update(string $hashcode, PeerToPeerFundraisingPageStoreFormRequest $request, Member $supporter): JsonResponse
    {
        $fundraisingPage = $supporter->fundraisingPages()
            ->standaloneType()
            ->hashid($hashcode)
            ->firstOrFail();

        if ($fundraisingPage->is_team) {
            $fundraisingPage->team_name = $request->team_name;
        }

        $fundraisingPage->title = $request->title;
        $fundraisingPage->goal_amount = numeral($request->goal_amount)->toFloat();
        $fundraisingPage->avatar_name = $request->avatar_name;
        $fundraisingPage->save();

        return $this->success(['fundraising_page' => FundraisingPageResource::make($fundraisingPage)]);
    }

    public function join(string $hashcode, Request $request, Member $supporter): JsonResponse
    {
        $teamFundraisingPage = FundraisingPage::query()
            ->standaloneType()
            ->where('is_team', true)
            ->hashid($hashcode)
            ->firstOrFail();

        if ($teamFundraisingPage->team_join_code !== $request->team_join_code) {
            return $this->failure();
        }

        $fundraisingPage = $supporter->fundraisingPages()
            ->where('team_fundraising_page_id', $teamFundraisingPage->id)
            ->where('member_organizer_id', $supporter->id)
            ->first();

        if (empty($fundraisingPage)) {
            $fundraisingPage = new FundraisingPage;
            $fundraisingPage->status = 'active';
            $fundraisingPage->product_id = $teamFundraisingPage->product_id;
            $fundraisingPage->member_organizer_id = $supporter->id;
            $fundraisingPage->type = FundraisingPageType::STANDALONE;
            $fundraisingPage->team_join_code = $request->team_join_code;
            $fundraisingPage->team_fundraising_page_id = $teamFundraisingPage->id;
            $fundraisingPage->currency_code = $teamFundraisingPage->currency_code;
            $fundraisingPage->activated_date = now();
        }

        $fundraisingPage->goal_amount = numeral($request->goal_amount)->toFloat();
        $fundraisingPage->avatar_name = $request->avatar_name;
        $fundraisingPage->save();

        return $this->success(['fundraising_page' => FundraisingPageResource::make($fundraisingPage)]);
    }
}
