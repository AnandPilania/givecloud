<?php

namespace Ds\Domain\Flatfile\Controllers;

use Ds\Domain\Flatfile\Jobs\ImportContributionsJob;
use Ds\Domain\Flatfile\Jobs\ImportSponsorshipsJob;
use Ds\Domain\Flatfile\Jobs\ImportSupportersJob;
use Illuminate\Http\JsonResponse;

class WebhookController
{
    public function contributions(): JsonResponse
    {
        ImportContributionsJob::dispatch(request()->input('scope.batchId'));

        return response()->json();
    }

    public function supporters(): JsonResponse
    {
        ImportSupportersJob::dispatch(request()->input('scope.batchId'));

        return response()->json();
    }

    public function sponsorships(): JsonResponse
    {
        ImportSponsorshipsJob::dispatch(request()->input('scope.batchId'));

        return response()->json();
    }
}
