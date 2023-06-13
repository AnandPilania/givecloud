<?php

namespace Ds\Listeners;

use Illuminate\Queue\Events\JobExceptionOccurred;

class LogJobException
{
    /**
     * Handle the event.
     *
     * @param \Illuminate\Queue\Events\JobExceptionOccurred $event
     * @return void
     */
    public function handle(JobExceptionOccurred $event)
    {
        notifyException($event->exception);
    }
}
