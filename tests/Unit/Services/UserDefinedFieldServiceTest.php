<?php

namespace Tests\Unit\Services;

use Ds\Models\Member;
use Ds\Models\Theme;
use Ds\Models\UserDefinedField;
use Ds\Services\UserDefinedFieldService;
use Exception;
use Tests\TestCase;

/**
 * @group backend
 * @group services
 * @group userdefinedfields
 */
class UserDefinedFieldServiceTest extends TestCase
{
    public function testSynchronizeOnNonUdfModelRaisesException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(Theme::class . ' does not support user defined fields.');

        $udf = UserDefinedField::factory()->radio()->create();
        $theme = Theme::factory()->create();

        $userDefinedFieldService = new UserDefinedFieldService;
        $userDefinedFieldService->synchronize($theme, [$udf->getKey() => 1]);
    }

    public function testSynchronizeWithoutFieldsSuccess(): void
    {
        $udf = UserDefinedField::factory()->radio()->create();
        $member = Member::factory()->create();
        $member->syncUserDefinedFields([$udf->getKey() => 0]);
        $this->assertCount(1, $member->refresh()->userDefinedFields);

        $userDefinedFieldService = new UserDefinedFieldService;
        $changes = $userDefinedFieldService->synchronize($member);

        $this->assertEmpty($member->refresh()->userDefinedFields);
        $this->assertSame(['attached' => [], 'detached' => [0 => $udf->getKey()], 'updated' => []], $changes);
    }

    public function testSynchronizeWithoutOptionSuccess(): void
    {
        $udf = UserDefinedField::factory()->radio()->create(['field_attributes' => []]);
        $member = Member::factory()->create();

        $userDefinedFieldService = new UserDefinedFieldService;
        $userDefinedFieldService->synchronize($member, [$udf->getKey() => 1]);

        $this->assertCount(1, $member->refresh()->userDefinedFields);
        $this->assertSame(1, $member->userDefinedFields->first()->pivot->value);
    }

    public function testSynchronizeWithOptionSuccess(): void
    {
        $udf = UserDefinedField::factory()->radio()->create(['field_attributes' => ['options' => range(1, 5)]]);
        $member = Member::factory()->create();

        $userDefinedFieldService = new UserDefinedFieldService;
        $userDefinedFieldService->synchronize($member, [$udf->getKey() => 2]);

        $this->assertCount(1, $member->refresh()->userDefinedFields);
        $this->assertSame(2, $member->refresh()->userDefinedFields->first()->pivot->value);
    }

    public function testSynchronizeWithUndefinedOptionFail(): void
    {
        $udf = UserDefinedField::factory()->radio()->create(['field_attributes' => ['options' => range(1, 5)]]);
        $member = Member::factory()->create();

        $userDefinedFieldService = new UserDefinedFieldService;
        $userDefinedFieldService->synchronize($member, [$udf->getKey() => 'not-an-option']);

        $this->assertEmpty($member->refresh()->userDefinedFields);
    }
}
