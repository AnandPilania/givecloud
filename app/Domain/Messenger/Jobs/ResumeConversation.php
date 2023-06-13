<?php

namespace Ds\Domain\Messenger\Jobs;

use Ds\Domain\Messenger\Models\ResumableConversation;
use Ds\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResumeConversation extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /** @var \Ds\Domain\Messenger\Models\ResumableConversation */
    protected $resumableConversation;

    /**
     * Create a new job instance.
     *
     * @param \Ds\Domain\Messenger\Models\ResumableConversation $resumableConversation
     * @return void
     */
    public function __construct(ResumableConversation $resumableConversation)
    {
        $this->resumableConversation = $resumableConversation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->resumableConversation->resume();
    }
}
