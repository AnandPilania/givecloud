<?php

namespace Tests\Unit\Mail;

use Ds\Mail\FundraisingPageEdited;
use Ds\Models\FundraisingPage;
use Tests\Concerns\InteractsWithMailables;
use Tests\TestCase;

class FundraisingPageEditedTest extends TestCase
{
    use InteractsWithMailables;

    public function testWithDeadline(): void
    {
        $page = FundraisingPage::factory()->create();

        $mailable = new FundraisingPageEdited($page->getMergeTags());
        $mailable->assertSeeInHtml('Deadline');

        $this->assertMailablePreview($mailable, 'with-deadline');
    }

    public function testWithOutDeadline(): void
    {
        $page = FundraisingPage::factory()->create(['goal_deadline' => null]);

        $mailable = new FundraisingPageEdited($page->getMergeTags());
        $mailable->assertDontSeeInHtml('Deadline');

        $this->assertMailablePreview($mailable, 'without-deadline');
    }
}
