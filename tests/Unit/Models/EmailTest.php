<?php

namespace Tests\Unit\Models;

use Ds\Models\Email;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EmailTest extends TestCase
{
    public function testEmailIsActiveIfDatesAreEmpty(): void
    {
        /** @var \Ds\Models\Email $email */
        $email = Email::factory()->create([
            'active_start_date' => '0000-00-00 00:00:00',
            'active_end_date' => '',
        ]);

        $this->assertFalse($email->isExpired());
        $this->assertFalse($email->is_expired);
    }

    public function testEmailIsNotYetActive(): void
    {
        /** @var \Ds\Models\Email */
        $email = Email::factory()->create([
            'active_start_date' => Carbon::tomorrow(),
            'active_end_date' => '',
        ]);

        $this->assertTrue($email->isNotYetActive());
        $this->assertTrue($email->isExpired());
        $this->assertTrue($email->is_expired);
    }

    public function testEmailIsNoLongerActive(): void
    {
        /** @var \Ds\Models\Email $email */
        $email = Email::factory()->create();

        $email->active_start_date = Carbon::yesterday();
        $email->active_end_date = Carbon::yesterday();

        $this->assertTrue($email->isNoLongerActive());
        $this->assertTrue($email->isExpired());
        $this->assertTrue($email->is_expired);
    }

    public function testEmailIsOffline(): void
    {
        /** @var \Ds\Models\Email $email */
        $email = Email::factory()->create([
            'active_start_date' => Carbon::yesterday(),
            'active_end_date' => Carbon::tomorrow(),
            'is_active' => false,
        ]);

        $this->assertTrue($email->isOffline());
        $this->assertTrue($email->isExpired());
        $this->assertTrue($email->is_expired);
    }

    public function testEmailIsDeleted(): void
    {
        /** @var \Ds\Models\Email $email */
        $email = Email::factory()->create([
            'active_start_date' => Carbon::yesterday(),
            'active_end_date' => Carbon::tomorrow(),
            'is_deleted' => true,
        ]);

        $this->assertTrue($email->isDeleted());
        $this->assertTrue($email->isExpired());
        $this->assertTrue($email->is_expired);
    }
}
