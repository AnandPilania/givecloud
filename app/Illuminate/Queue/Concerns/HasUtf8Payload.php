<?php

namespace Ds\Illuminate\Queue\Concerns;

use Illuminate\Support\Str;

trait HasUtf8Payload
{
    /**
     * Create a payload for an object-based queue handler.
     *
     * @param object $job
     * @param string $queue
     * @return array
     */
    protected function createObjectPayload($job, $queue)
    {
        $payload = $this->withCreatePayloadHooks($queue, [
            'uuid' => (string) Str::uuid(),
            'displayName' => $this->getDisplayName($job),
            'job' => 'Ds\Illuminate\Queue\CallQueuedHandler@call',
            'maxTries' => $job->tries ?? null,
            'maxExceptions' => $job->maxExceptions ?? null,
            'failOnTimeout' => $job->failOnTimeout ?? false,
            'backoff' => $this->getJobBackoff($job),
            'timeout' => $job->timeout ?? null,
            'retryUntil' => $this->getJobExpiration($job),
            'data' => [
                'commandName' => $job,
                'command' => $job,
            ],
        ]);

        $command = $this->jobShouldBeEncrypted($job) && $this->container->bound(Encrypter::class)
            ? $this->container[Encrypter::class]->encrypt(serialize(clone $job))
            : serialize(clone $job);

        return array_merge($payload, [
            'data' => [
                'commandName' => get_class($job),
                'command' => utf8_encode($command),
            ],
        ]);
    }
}
