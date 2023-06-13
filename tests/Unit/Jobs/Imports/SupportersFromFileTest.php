<?php

namespace Tests\Unit\Jobs\Imports;

use Ds\Jobs\Import\SupportersFromFile;
use Ds\Models\Member;
use Ds\Models\Membership;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Concerns\InteractsWithImports;
use Tests\TestCase;

class SupportersFromFileTest extends TestCase
{
    use InteractsWithImports;
    use WithFaker;

    public function testGetColumnDefinitions(): void
    {
        $this->assertImportJobColumnDefinitions($this->app->make(SupportersFromFile::class));
    }

    /**
     * @dataProvider membershipImportedWithCorrectStartDateProvider
     */
    public function testMembershipImportedWithCorrectStartDate(string $expectedStartDate, ?string $startDate): void
    {
        $supporter = Member::factory()->create([
            'email' => $this->faker->unique()->email,
            'created_at' => fromLocal('2018-10-02')->toUtc(),
        ]);

        $membership = Membership::factory()->create();

        $this->app->make(SupportersFromFile::class)->importRow([
            'email' => $supporter->email,
            'membership_name' => $membership->name,
            'membership_starts_on' => $startDate,
        ]);

        $this->assertSame($expectedStartDate, $supporter->membership->pivot->start_date->toDateFormat());
    }

    public function membershipImportedWithCorrectStartDateProvider(): array
    {
        return [
            ['2012-05-21', '2012-05-21'],
            ['2018-10-02', null],
        ];
    }
}
