<?php

namespace Ds\Http\Controllers\Frontend\API;

use Ds\Domain\Analytics\AnalyticsService;
use Ds\Http\Requests\Frontend\API\AnalyticsFormRequest;
use Ds\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class AnalyticsController extends Controller
{
    public function __invoke(AnalyticsService $analytics, AnalyticsFormRequest $request): Response
    {
        foreach ($request->input('events') as $event) {
            [$eventableType, $eventableId] = explode('_', $event['eventable'], 2);

            $product = Product::donationForms()
                ->hashid($eventableId)
                ->firstOrFail();

            $analytics->collectEvent($product, Arr::except($event, 'eventable'), $request);
        }

        return response()->noContent();
    }
}
