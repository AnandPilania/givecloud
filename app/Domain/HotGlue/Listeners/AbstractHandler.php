<?php

namespace Ds\Domain\HotGlue\Listeners;

use Ds\Domain\HotGlue\HotGlue;
use Ds\Domain\HotGlue\Targets\AbstractTarget;
use Ds\Events\Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

abstract class AbstractHandler implements ShouldQueue
{
    abstract public function state(Event $event): array;

    abstract public function target(): AbstractTarget;

    public function handle(Event $event): void
    {
        try {
            app(HotGlue::class)
                ->client()
                ->post($this->target()->url(), [
                    'tap' => 'api',
                    'state' => $this->state($event),
                ])->throw();
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function shouldQueue(): bool
    {
        return $this->target()->isEnabled()
            && $this->target()->isLinked()
            && $this->target()->isConnected();
    }

    public function url(): string
    {
        return $this->target()->url();
    }
}
