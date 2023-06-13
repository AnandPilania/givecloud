<?php

namespace Ds\Illuminate\Log;

use Ds\Models\TransientLog;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class TransientLogHandler extends AbstractProcessingHandler
{
    /** @var string */
    protected $source;

    public function __construct(string $source, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->source = $source;

        parent::__construct($level, $bubble);
    }

    protected function write(array $report): void
    {
        TransientLog::create([
            'origin' => App::runningInConsole() ? 'console' : 'web',
            'level' => strtolower($report['level_name']),
            'request_id' => $this->generateRequestId(),
            'user_id' => optional(Auth::user())->getKey(),
            'source' => $this->source,
            'message' => $report['message'],
            'context' => empty($report['context']) ? null : json_encode($report['context']),
            'ip_address' => Request::ip(),
        ]);
    }

    protected function generateRequestId(): string
    {
        return reqcache('transient-log-handler:request-id', function () {
            return Str::orderedUuid();
        });
    }
}
