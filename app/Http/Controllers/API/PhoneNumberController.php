<?php

namespace Ds\Http\Controllers\API;

use Ds\Domain\Messenger\Models\ConversationRecipient;
use Ds\Domain\Messenger\Support\Twilio;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Http\Controllers\Controller;
use Ds\Http\Requests\PhoneNumberDestroyFormRequest;
use Ds\Http\Requests\PhoneNumberStoreFormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class PhoneNumberController extends Controller
{
    /** @var \Ds\Domain\Messenger\Support\Twilio */
    private $twilio;

    public function __construct(Twilio $twilio)
    {
        parent::__construct();

        $this->twilio = $twilio;
    }

    public function store(PhoneNumberStoreFormRequest $request): JsonResponse
    {
        try {
            return response()->json(
                $this->twilio
                    ->provisionPhoneNumber($request->phone_number)
                    ->identifier
            );
        } catch (Throwable $e) {
            return response()->json(
                "An error occured when provisionning $request->phone_number",
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    public function destroy(
        PhoneNumberDestroyFormRequest $request,
        ConversationRecipient $conversationRecipient
    ): JsonResponse {
        try {
            if ($this->twilio->releasePhoneNumber($conversationRecipient)) {
                return response()->json(null, Response::HTTP_NO_CONTENT);
            }
        } catch (MessageException $e) {
            $errorMessage = $e->getMessage();
        } catch (Throwable $e) {
            report($e);
        }

        return response()->json(
            $errorMessage ?? "An error occured when releasing $conversationRecipient->identifier",
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
