<?php

namespace Ds\Common\Exceptionist;

use Illuminate\Support\Arr;
use Monolog\Formatter\JsonFormatter;
use Throwable;

class DatadogLogger
{
    /**
     * Customize the given logger instance.
     *
     * @param \Illuminate\Log\Logger $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(function (array $record) {
                return $this->includeMetaData($record);
            });

            $handler->setFormatter(new JsonFormatter);
        }
    }

    /**
     * Include addition metadata in log.
     *
     * @param array $record
     * @return array
     */
    public function includeMetaData(array $record): array
    {
        Arr::set($record, 'extra.site', sys_get('ds_account_name'));

        try {
            if ($site = site()) {
                Arr::set($record, 'extra.version', $site->version);
            }
        } catch (Throwable $e) {
            // Ignore any errors.
        }

        try {
            if ($user = auth()->user()) {
                Arr::set($record, 'extra.user', [
                    'id' => $user->id,
                    'first_name' => $user->firstname,
                    'last_name' => $user->lastname,
                    'email' => $user->email,
                ]);
            }
        } catch (Throwable $e) {
            // Ignore any errors.
        }

        try {
            if (function_exists('member') && member()) {
                Arr::set($record, 'extra.member', [
                    'id' => member('id'),
                    'first_name' => member('first_name'),
                    'last_name' => member('last_name'),
                    'email' => member('email'),
                ]);
            }
        } catch (Throwable $e) {
            // Ignore any errors.
        }

        // @see: https://docs.datadoghq.com/tracing/advanced/connect_logs_and_traces/?tab=php
        if (class_exists('DDTrace\GlobalTracer')) {
            try {
                if ($span = \DDTrace\GlobalTracer::get()->getActiveSpan()) {
                    Arr::set($record, 'extra.span_id', $span->getSpanId());
                    Arr::set($record, 'extra.trace_id', $span->getTraceId());
                }
            } catch (Throwable $e) {
                // Ignore any errors.
            }
        }

        return $record;
    }
}
