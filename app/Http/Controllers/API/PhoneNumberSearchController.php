<?php

namespace Ds\Http\Controllers\API;

use Ds\Domain\Messenger\Support\Twilio;
use Ds\Http\Controllers\Controller;
use Ds\Http\Requests\PhoneNumberSearchFormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class PhoneNumberSearchController extends Controller
{
    /** @var \Ds\Domain\Messenger\Support\Twilio */
    private $twilio;

    public function __construct(Twilio $twilio)
    {
        parent::__construct();

        $this->twilio = $twilio;
    }

    public function __invoke(PhoneNumberSearchFormRequest $request): JsonResponse
    {
        $phoneNumbers = $this->searchAvailablePhoneNumbers($request);

        if ($phoneNumbers->isEmpty()) {
            return response()->json(null, Response::HTTP_NO_CONTENT);
        }

        return response()->json($phoneNumbers->map(function ($phoneNumber) {
            return (object) [
                'full' => $phoneNumber->phoneNumber,
                'short' => $phoneNumber->friendlyName,
            ];
        }));
    }

    protected function searchAvailablePhoneNumbers(PhoneNumberSearchFormRequest $request): Collection
    {
        if ($request->isTollFree()) {
            return $this->twilio->searchTollFreePhoneNumbers($request->country);
        }

        return $this->twilio->searchPhoneNumbers($request->area_code, $request->country);
    }
}
