<?php

namespace Tests\Unit\Illuminate\Log;

use Ds\Illuminate\Log\TransientLogHandler;
use Ds\Models\TransientLog;
use Illuminate\Foundation\Testing\WithFaker;
use Monolog\DateTimeImmutable;
use Monolog\Logger;
use Tests\TestCase;

class TransientLogHandlerTest extends TestCase
{
    use WithFaker;

    public function testHandlerCreatesTransientLog()
    {
        (new TransientLogHandler('test_case'))->handle($this->generateReport(
            $message = $this->faker->sentence
        ));

        $logs = TransientLog::take(2)->get();

        $this->assertCount(1, $logs);
        $this->assertSame($message, $logs[0]->message);
    }

    private function generateReport(string $message): array
    {
        return [
            'message' => (string) $message,
            'context' => [],
            'level' => Logger::INFO,
            'level_name' => Logger::getLevelName(Logger::INFO),
            'channel' => 'test',
            'datetime' => new DateTimeImmutable(true),
            'extra' => [],
        ];
    }
}
