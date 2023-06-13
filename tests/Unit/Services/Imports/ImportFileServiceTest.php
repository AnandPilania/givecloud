<?php

namespace Tests\Unit\Services\Imports;

use Ds\Models\Import;
use Ds\Services\Imports\ImportService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** @group Imports */
class ImportFileServiceTest extends TestCase
{
    /** @dataProvider firstRowOfHeadersDataProvider */
    public function testFirstRowOfHeaders(bool $hasHeaders, int $expectedFirstRow): void
    {
        $import = Import::factory()->make(['file_has_headers' => $hasHeaders]);

        $this->assertSame($this->app->make(ImportService::class)->firstRowOfData($import), $expectedFirstRow);
    }

    public function firstRowOfHeadersDataProvider(): array
    {
        return [
            [false, 1],
            [true, 2],
        ];
    }

    /** @dataProvider stageAttributesDataProvioder */
    public function testGetStepForStageReturnsStage(array $attributes, int $expectedState): void
    {
        $import = Import::factory()->make($attributes);
        $this->assertSame($this->app->make(ImportService::class)->getStepForStage($import), $expectedState);
    }

    public function stageAttributesDataProvioder(): array
    {
        return [
            [[], 1],
            [['file' => 'some_file.csv'], 2],
            [['stage' => 'analysis_queue'], 3],
            [['stage' => 'aborted'], 3],
            [['stage' => 'done'], 4],
        ];
    }

    public function testResetAnalysisResetsColumns(): void
    {
        $import = Import::factory()->create([
            'analysis_started_at' => Carbon::yesterday(),
            'analysis_ended_at' => Carbon::yesterday(),
            'analyzed_ok_records' => '100',
            'analyzed_warning_records' => '1',
            'current_record' => '10',
            'stage' => 'aborted',
        ]);

        $this->app->make(ImportService::class)->resetAnalysis($import);

        $import->refresh();

        $this->assertNull($import->analysis_started_at);
        $this->assertNull($import->analysis_ended_at);
        $this->assertSame('draft', $import->stage);
        $this->assertSame(0, $import->analyzed_ok_records);
        $this->assertSame(0, $import->analyzed_warning_records);
        $this->assertSame(0, $import->current_record);
    }
}
