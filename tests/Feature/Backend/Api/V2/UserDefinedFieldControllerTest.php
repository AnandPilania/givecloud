<?php

namespace Tests\Feature\Backend\Api\V2;

use Ds\Http\Resources\UserDefinedFieldResource;
use Ds\Models\UserDefinedField;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * @group backend
 * @group userdefinedfields
 */
class UserDefinedFieldControllerTest extends TestCase
{
    public function testIndexSuccess()
    {
        $userDefinedFields = UserDefinedField::factory()->count(3)->create();

        $this->actingAsUserWithUserDefinedFiledsPermission();
        $response = $this->getJson(route('admin.api.v2.user_defined_fields.index'));

        $response->assertOk();
        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertExact((array) $this->udfResourceCollectionArray($userDefinedFields));
        $jsonResponseArray = (array) $jsonResponse->json();
        $userDefinedFields->each(function ($udf) use ($jsonResponseArray) {
            $this->assertArrayHasArrayWithValue($jsonResponseArray, $udf->name);
        });
    }

    public function testIndexForbidden()
    {
        UserDefinedField::factory(3)->create();

        $this->actingAsPassportUser()
            ->getJson(route('admin.api.v2.user_defined_fields.index'))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testStoreSuccess()
    {
        $userDefinedField = UserDefinedField::factory()->make();

        $this->actingAsUserWithUserDefinedFiledsPermission();
        $response = $this->postJson(route('admin.api.v2.user_defined_fields.store'), $userDefinedField->toArray());

        $response->assertCreated();
        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertFragment($this->udfResourceArray(
            $this->findFirstUdfFromAttributes($userDefinedField)
        ));
    }

    public function testStoreForbidden()
    {
        $this->actingAsPassportUser()
            ->postJson(route('admin.api.v2.user_defined_fields.store'), UserDefinedField::factory()->make()->toArray())
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertSame(0, UserDefinedField::count());
    }

    /**
     * @dataProvider storeValidationFailsDataProvider
     */
    public function testStoreValidationFails(array $badData = [], array $errorMessages = [])
    {
        $this->actingAsUserWithUserDefinedFiledsPermission();

        $response = $this->postJson(
            route('admin.api.v2.user_defined_fields.store'),
            $badData ? array_merge(UserDefinedField::factory()->make()->toArray(), $badData) : []
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame(0, UserDefinedField::count());

        $responseErrorsArray = collect($response->decodeResponseJson()->json('errors'))->flatten();
        $this->assertCount(count($errorMessages), $responseErrorsArray);
        $responseErrorsArray->each(function ($error) use ($errorMessages) {
            $this->assertTrue(in_array($error, $errorMessages), "Error '$error' was not expected.");
        });
    }

    /**
     * Data that will trigger the given error message when store validation fails.
     */
    public function storeValidationFailsDataProvider(): array
    {
        return [
            [
                [],
                [
                    'The entity field is required.',
                    'The field attributes field is required.',
                    'The field type field is required.',
                    'The name field is required.',
                ],
            ],
            [
                ['entity' => 'unknown-entity'],
                ['The selected entity is invalid.'],
            ],
            [
                ['field_attributes' => 'not an array'],
                ['The field attributes must be an array.'],
            ],
            [
                ['field_type' => 'unknown-field-type'],
                ['The selected field type is invalid.'],
            ],
        ];
    }

    public function testUpdateSuccess()
    {
        $userDefinedField = UserDefinedField::factory()->create();
        $newUserDefinedField = UserDefinedField::factory()->make();

        $this->actingAsUserWithUserDefinedFiledsPermission();
        $response = $this->putJson(
            route('admin.api.v2.user_defined_fields.update', $userDefinedField),
            $newUserDefinedField->toArray()
        );

        $response->assertOk();
        $jsonResponse = $response->decodeResponseJson();
        $newUserDefinedField = $this->findFirstUdfFromAttributes($newUserDefinedField);
        $jsonResponse->assertFragment($this->udfResourceArray($newUserDefinedField));

        $this->assertEquals(
            $this->udfResourceArray($newUserDefinedField),
            $this->udfResourceArray($userDefinedField->refresh())
        );
    }

    public function testUpdateForbidden()
    {
        $userDefinedField = UserDefinedField::factory()->create();
        $newUserDefinedField = UserDefinedField::factory()->make();

        $this->actingAsPassportUser()
            ->putJson(route('admin.api.v2.user_defined_fields.update', $userDefinedField), $newUserDefinedField->toArray())
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertNotEquals(
            $this->udfResourceArray($newUserDefinedField),
            $this->udfResourceArray($userDefinedField->refresh())
        );
    }

    public function testUpdateNotFoundFails()
    {
        $this->actingAsUserWithUserDefinedFiledsPermission()
            ->putJson(route('admin.api.v2.user_defined_fields.update', 0), [])
            ->assertNotFound();
    }

    /**
     * @dataProvider updateValidationFailsDataProvider
     */
    public function testUpdateValidationFails(array $badData = [], array $errorMessages = [])
    {
        $this->actingAsUserWithUserDefinedFiledsPermission();

        $response = $this->postJson(
            route('admin.api.v2.user_defined_fields.store'),
            $badData ? array_merge(UserDefinedField::factory()->make()->toArray(), $badData) : []
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame(0, UserDefinedField::count());

        $responseErrorsArray = collect($response->decodeResponseJson()->json('errors'))->flatten();
        $this->assertCount(count($errorMessages), $responseErrorsArray);
        $responseErrorsArray->each(function ($error) use ($errorMessages) {
            $this->assertTrue(in_array($error, $errorMessages), "Error '$error' was not expected.");
        });
    }

    /**
     * Data that will trigger the given error message when store validation fails.
     */
    public function updateValidationFailsDataProvider(): array
    {
        return [
            [
                [],
                [
                    'The entity field is required.',
                    'The field attributes field is required.',
                    'The field type field is required.',
                    'The name field is required.',
                ],
            ],
            [
                ['entity' => 'unknown-entity'],
                ['The selected entity is invalid.'],
            ],
            [
                ['field_attributes' => 'not an array'],
                ['The field attributes must be an array.'],
            ],
            [
                ['field_type' => 'unknown-field-type'],
                ['The selected field type is invalid.'],
            ],
        ];
    }

    public function testShowSuccess()
    {
        $userDefinedField = UserDefinedField::factory()->create();

        $this->actingAsUserWithUserDefinedFiledsPermission();
        $response = $this->getJson(route('admin.api.v2.user_defined_fields.show', $userDefinedField));

        $response->assertOk();
        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertFragment($this->udfResourceArray($userDefinedField));
    }

    public function testShowNotFoundFails()
    {
        $this->actingAsUserWithUserDefinedFiledsPermission()
            ->getJson(route('admin.api.v2.user_defined_fields.show', 0))
            ->assertNotFound();
    }

    public function testShowForbidden()
    {
        $this->actingAsPassportUser()
            ->getJson(route('admin.api.v2.user_defined_fields.show', UserDefinedField::factory()->create()))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteSuccess()
    {
        $userDefinedField = UserDefinedField::factory()->create();

        $this->actingAsUserWithUserDefinedFiledsPermission();
        $response = $this->deleteJson(route('admin.api.v2.user_defined_fields.destroy', $userDefinedField));

        $response->assertOk();
        $this->assertSame(0, UserDefinedField::count());
    }

    public function testDeleteNotFoundFails()
    {
        $this->actingAsUserWithUserDefinedFiledsPermission()
            ->deleteJson(route('admin.api.v2.user_defined_fields.destroy', 0))
            ->assertNotFound();
    }

    public function testDeleteForbidden()
    {
        $userDefinedField = UserDefinedField::factory()->create();

        $this->actingAsPassportUser()
            ->deleteJson(route('admin.api.v2.user_defined_fields.destroy', $userDefinedField))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertSame(1, UserDefinedField::count());
    }

    protected function actingAsUserWithUserDefinedFiledsPermission()
    {
        return $this->actingAsPassportUser($this->createUserWithPermissions('userdefinedfields.'));
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function findFirstUdfFromAttributes(UserDefinedField $userDefinedField): UserDefinedField
    {
        return UserDefinedField::where('name', $userDefinedField->name)
            ->where('entity', $userDefinedField->entity)
            ->whereJsonContains('field_attributes', $userDefinedField->field_attributes)
            ->where('field_type', $userDefinedField->field_type)
            ->firstOrFail();
    }

    protected function udfResource(UserDefinedField $userDefinedField): UserDefinedFieldResource
    {
        return new UserDefinedFieldResource($userDefinedField);
    }

    protected function udfResourceArray(UserDefinedField $userDefinedField): array
    {
        return $this->udfResource($userDefinedField)->toArray(new Request());
    }

    protected function udfResourceCollection(Collection $userDefinedFields): AnonymousResourceCollection
    {
        return UserDefinedFieldResource::collection($userDefinedFields);
    }

    protected function udfResourceCollectionArray(Collection $userDefinedFields): array
    {
        return $this->udfResourceCollection($userDefinedFields)->toArray(new Request());
    }
}
