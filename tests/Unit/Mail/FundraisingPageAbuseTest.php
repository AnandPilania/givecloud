<?php

namespace Tests\Unit\Mail;

use Ds\Mail\FundraisingPageAbuse;
use Ds\Models\FundraisingPage;
use Ds\Models\FundraisingPageReport;
use Tests\Concerns\InteractsWithMailables;
use Tests\TestCase;

class FundraisingPageAbuseTest extends TestCase
{
    use InteractsWithMailables;

    public function testSingleReportCount(): void
    {
        $page = FundraisingPage::factory()->create();

        $page->reports()->save($report = FundraisingPageReport::factory()->make());
        $page->updateAggregates();

        $mailable = new FundraisingPageAbuse($page->refresh()->getMergeTags([
            'page_report_reason' => $report->reason,
        ]));

        $mailable->assertDontSeeInHtml("There are a total of <strong>{$page->report_count}</strong> abuse reports for this page.");

        $this->assertMailablePreview($mailable, 'single-report');
    }

    public function testMultipleReportCounts(): void
    {
        $page = FundraisingPage::factory()->create();

        $page->reports()->saveMany([
            FundraisingPageReport::factory()->make(),
            FundraisingPageReport::factory()->make(),
            FundraisingPageReport::factory()->make(),
            $report = FundraisingPageReport::factory()->make(),
        ]);

        $page->updateAggregates();

        $mailable = new FundraisingPageAbuse($page->getMergeTags([
            'page_report_reason' => $report->reason,
        ]));

        $mailable->assertSeeInHtml("There are a total of <strong>{$page->report_count}</strong> abuse reports for this page.");

        $this->assertMailablePreview($mailable, 'multiple-reports');
    }
}
