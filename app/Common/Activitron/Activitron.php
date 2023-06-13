<?php

namespace Ds\Common\Activitron;

use Closure;
use DataDog\DogStatsd;
use Intercom\IntercomClient;
use Throwable;

class Activitron
{
    /** @var \DataDog\DogStatsd */
    protected $dogstatsd;

    /** @var \Intercom\IntercomClient */
    protected $intercom;

    /** @var array */
    protected $timings = [];

    /** @var array */
    protected $memoryUsages = [];

    /**
     * Create an instance.
     *
     * @param \DataDog\DogStatsd $dogstatsd
     * @param \Intercom\IntercomClient $intercom
     */
    public function __construct(DogStatsd $dogstatsd, IntercomClient $intercom)
    {
        $this->dogstatsd = $dogstatsd;
        $this->intercom = $intercom;
    }

    /**
     * Increment the counter
     *
     * @param string $metric
     * @param int $count
     * @param float $sampleRate
     * @param array $tags
     */
    public function increment(string $metric, int $count = 1, float $sampleRate = 1.0, array $tags = [])
    {
        $tags = $this->includeGlobalTags($tags);

        $this->dogstatsd->updateStats($metric, $count, $sampleRate, $tags);
    }

    /**
     * Decrement the counter
     *
     * @param string $metric
     * @param int $count
     * @param float $sampleRate
     * @param array $tags
     */
    public function decrement(string $metric, int $count = 1, float $sampleRate = 1.0, array $tags = [])
    {
        $tags = $this->includeGlobalTags($tags);

        $this->dogstatsd->updateStats($metric, 0 - $count, $sampleRate, $tags);
    }

    /**
     * Gauge an arbitrary persistent value
     *
     * @param string $metric
     * @param float $value
     * @param float $sampleRate
     * @param array $tags
     */
    public function gauge(string $metric, float $value, float $sampleRate = 1.0, array $tags = [])
    {
        $tags = $this->includeGlobalTags($tags);

        $this->dogstatsd->gauge($metric, $value, $sampleRate, $tags);
    }

    /**
     * Report the number of unique elements
     *
     * @param string $metric
     * @param mixed $value
     * @param float $sampleRate
     * @param array $tags
     */
    public function set(string $metric, $value, float $sampleRate = 1.0, array $tags = [])
    {
        $value = (float) $value;
        $tags = $this->includeGlobalTags($tags);

        $this->dogstatsd->set($metric, $value, $sampleRate, $tags);
    }

    /**
     * Track the timing
     *
     * @param string $metric
     * @param float|null $time
     */
    public function startTiming(string $metric, $time = null)
    {
        $this->timings[$metric] = $time ?? microtime(true);
    }

    /**
     * Measure and report the amount of time (ms) an action took to complete
     *
     * @param string $metric
     * @param float $sampleRate
     * @param array $tags
     */
    public function endTiming(string $metric, float $sampleRate = 1.0, array $tags = []): ?float
    {
        $tags = $this->includeGlobalTags($tags);

        if (array_key_exists($metric, $this->timings)) {
            $time = (microtime(true) - $this->timings[$metric]) * 1000;
            unset($this->timings[$metric]);

            $this->timing($metric, $time, $sampleRate, $tags);

            return $time;
        }

        return null;
    }

    /**
     * Measure and report the amount of time (ms) a block took to complete
     *
     * @param string $metric
     * @param \Closure $block
     * @param float $sampleRate
     * @param array $tags
     */
    public function time(string $metric, Closure $block = null, float $sampleRate = 1.0, array $tags = [])
    {
        $this->startTiming($metric);

        if ($block) {
            try {
                return $block();
            } finally {
                $tags = $this->includeGlobalTags($tags);

                $this->endTiming($metric, $sampleRate, $tags);
            }
        }
    }

    /**
     * Report the amount of time (ms) an action took to complete
     *
     * @param string $metric
     * @param float $time
     * @param float $sampleRate
     * @param array $tags
     */
    public function timing(string $metric, $time = null, float $sampleRate = 1.0, array $tags = [])
    {
        $tags = $this->includeGlobalTags($tags);

        if ($time === null) {
            $time = (microtime(true) - LARAVEL_START) * 1000;
        } else {
            $time = (float) $time;
        }

        $this->dogstatsd->timing($metric, $time, $sampleRate, $tags);
    }

    /**
     * Report a value to be aggregated as a histogram
     *
     * @param string $metric
     * @param float $value
     * @param float $sampleRate
     * @param array $tags
     */
    public function histogram(string $metric, float $value, float $sampleRate = 1.0, array $tags = [])
    {
        $tags = $this->includeGlobalTags($tags);

        $this->dogstatsd->histogram($metric, $value, $sampleRate, $tags);
    }

    /**
     * Track memory usage (bytes)
     *
     * @param string $metric
     */
    public function startMemoryProfile(string $metric)
    {
        $this->memoryUsages[$metric] = memory_get_usage();
    }

    /**
     * Measure and report memory usage (bytes) while an action completed
     *
     * @param string $metric
     * @param float $sampleRate
     * @param array $tags
     */
    public function endMemoryProfile(string $metric, float $sampleRate = 1.0, array $tags = [])
    {
        $tags = $this->includeGlobalTags($tags);

        if (array_key_exists($metric, $this->memoryUsages)) {
            $memory = memory_get_usage() - $this->memoryUsages[$metric];
            unset($this->memoryUsages[$metric]);

            $this->memory($metric, $memory, $sampleRate, $tags);
        }
    }

    /**
     * Report memory usage (bytes) while an action completed
     *
     * @param string $metric
     * @param int $memory
     * @param float $sampleRate
     * @param array $tags
     */
    public function memory(string $metric, $memory = null, float $sampleRate = 1.0, array $tags = [])
    {
        $tags = $this->includeGlobalTags($tags);

        if ($memory === null) {
            $memory = memory_get_peak_usage();
        } else {
            $memory = (int) $memory;
        }

        $this->gauge($metric, $memory, $sampleRate, $tags);
    }

    /**
     * Send a StatsD datagram (ex. <bucket>:<value>|<type>|@<sample rate>)
     *
     * @param string $datagram
     */
    public function datagram(string $datagram)
    {
        $this->dogstatsd->report($datagram);
    }

    /**
     * Send an event (potentially slow)
     *
     * See: https://docs.datadoghq.com/guides/dogstatsd/#events-1
     *
     * @param array $data
     */
    public function event(array $data = [])
    {
        $data = [
            'title' => $data['title'] ?? null,
            'text' => $data['text'] ?? null,
            'priority' => $data['priority'] ?? null,
            'alert_type' => $data['alert_type'] ?? null,
            'tags' => $this->includeGlobalTags($data['tags'] ?? []),
        ];

        if (empty($data['title']) || empty($data['text'])) {
            return;
        }

        $this->dogstatsd->event($data['title'], $data);
    }

    /**
     * Send an event (potentially slow)
     *
     * See: https://developers.intercom.com/reference#submitting-events
     *
     * @param string $name
     * @param array $metadata
     */
    public function supportEvent(string $name, array $metadata = [])
    {
        $email = user('email');

        if ($email) {
            $this->intercom->events->create([
                'event_name' => $name,
                'created_at' => gmstrftime('%s'),
                'email' => $email,
                'metadata' => $this->includeGlobalTags($metadata),
            ]);
        }
    }

    /**
     * Send an exception event (potentially slow)
     *
     * @param \Throwable $e
     */
    public function exception(Throwable $e)
    {
        $data = [
            'title' => 'Exception',
            'text' => $e->getMessage(),
            'priority' => null,
            'alert_type' => null,
            'tags' => [
                'exception_class' => get_class($e),
            ],
        ];

        $this->event($data);
    }

    /**
     * Adds global tags to an array of tags
     *
     * @param array $tags
     * @return array
     */
    protected function includeGlobalTags(array $tags = [])
    {
        $tags['app_env'] = app()->environment();

        if (function_exists('sys_get')) {
            $tags['site'] = sys_get('ds_account_name');
        }

        return $tags;
    }
}
