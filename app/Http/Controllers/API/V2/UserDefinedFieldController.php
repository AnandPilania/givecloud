<?php

namespace Ds\Http\Controllers\API\V2;

use Ds\Http\Requests\UserDefinedFieldStoreFormRequest;
use Ds\Http\Requests\UserDefinedFieldUpdateFormRequest;
use Ds\Http\Resources\UserDefinedFieldResource;
use Ds\Models\UserDefinedField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserDefinedFieldController extends Controller
{
    public function index(): JsonResponse
    {
        user()->canOrRedirect('userdefinedfields');

        return response()->json(UserDefinedFieldResource::collection(UserDefinedField::all()));
    }

    /**
     * @throws \Throwable
     */
    public function store(UserDefinedFieldStoreFormRequest $request): JsonResponse
    {
        user()->canOrRedirect('userdefinedfields');

        $newUserDefinedField = UserDefinedField::createOrFail(
            $request->only('entity', 'field_attributes', 'field_type', 'name')
        );

        return response()->json(new UserDefinedFieldResource($newUserDefinedField), Response::HTTP_CREATED);
    }

    public function show(UserDefinedField $userDefinedField): JsonResponse
    {
        user()->canOrRedirect('userdefinedfields');

        return response()->json(new UserDefinedFieldResource($userDefinedField));
    }

    /**
     * @throws \Throwable
     */
    public function update(UserDefinedFieldUpdateFormRequest $request, UserDefinedField $userDefinedField): JsonResponse
    {
        user()->canOrRedirect('userdefinedfields');

        // If any field has a null value it means unchanged.
        $userDefinedField->updateOrFail([
            'entity' => $request->get('entity', $userDefinedField->entity),
            'field_attributes' => $request->get('field_attributes', $userDefinedField->field_attributes),
            'field_type' => $request->get('field_type', $userDefinedField->field_type),
            'name' => $request->get('name', $userDefinedField->name),
        ]);

        return response()->json(new UserDefinedFieldResource($userDefinedField));
    }

    /**
     * @throws \Exception
     */
    public function destroy(UserDefinedField $userDefinedField): JsonResponse
    {
        user()->canOrRedirect('userdefinedfields');

        return response()->json([], $userDefinedField->delete() ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
