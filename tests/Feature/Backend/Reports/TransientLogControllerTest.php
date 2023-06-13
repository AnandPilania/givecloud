<?php

namespace Tests\Feature\Backend\Reports;

use Ds\Models\TransientLog;
use Tests\TestCase;

class TransientLogControllerTest extends TestCase
{
    public function testOnlySuperUserCanAccess(): void
    {
        $this->createTransientLogs();

        $this->actingAsAdminUser()
            ->post(route('backend.reports.transient_logs.get'))
            ->assertRedirect()
            ->assertSessionHasFlashMessages('error', 'This feature is restricted.');
    }

    /**
     * @dataProvider canFilterWithSearchProvider
     */
    public function testCanFilterWithSearch(int $expected, array $parameters): void
    {
        $this->createTransientLogs();

        $this->actingAsSuperUser()
            ->post(route('backend.reports.transient_logs.get'), $parameters)->assertOk()
            ->assertJsonCount($expected, 'data');
    }

    public function canFilterWithSearchProvider(): array
    {
        return [
            [1, ['search' => 'little lamb']],
            [1, ['search' => '93a703ef-14ab']],
        ];
    }

    /**
     * @dataProvider canFilterOnCreatedAtProvider
     */
    public function testCanFilterOnCreatedAt(int $expected, array $parameters): void
    {
        $this->createTransientLogs();

        $this->actingAsSuperUser()
            ->post(route('backend.reports.transient_logs.get'), $parameters)->assertOk()
            ->assertJsonCount($expected, 'data');
    }

    public function canFilterOnCreatedAtProvider(): array
    {
        return [
            [4, ['gte_created' => '2 days ago']],
            [9, ['lte_created' => '2 days ago']],
            [5, ['gte_created' => '8 days ago', 'lte_created' => '4 days ago']],
        ];
    }

    private function createTransientLogs(): void
    {
        $date = now()->subDays(11);

        TransientLog::factory(11)->state(function ($attributes) use ($date) {
            return ['created_at' => $date->addDay()];
        })->create();

        TransientLog::factory()->create([
            'request_id' => '93a703ef-14ab-419f-9339-0b3f762fbe15',
            'message' => 'Mary had a little lamb',
            'created_at' => $date->addDay(),
        ]);
    }
}
