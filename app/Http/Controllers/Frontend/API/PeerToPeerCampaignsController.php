<?php

namespace Ds\Http\Controllers\Frontend\API;

use Ds\Http\Resources\PeerToPeer\FundraisingPageResource;
use Ds\Models\FundraisingPage;
use Illuminate\Http\JsonResponse;

class PeerToPeerCampaignsController extends Controller
{
    public function get(string $hashcode): JsonResponse
    {
        $fundraisingPage = FundraisingPage::query()
            ->standaloneType()
            ->hashid($hashcode)
            ->firstOrFail();

        return $this->success(['fundraising_page' => FundraisingPageResource::make($fundraisingPage)]);
    }

    public function validateTeamJoinCode(string $hashid): JsonResponse
    {
        $fundraisingPage = FundraisingPage::query()
            ->standaloneType()
            ->hashid($hashid)
            ->first();

        if (empty($fundraisingPage)) {
            return $this->success(['valid' => false]);
        }

        return $this->success([
            'valid' => $fundraisingPage->team_join_code === request('code'),
        ]);
    }
}
