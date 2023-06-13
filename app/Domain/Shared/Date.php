<?php

namespace Ds\Domain\Shared;

use DateTimeZone;

class Date extends DateTime
{
    /**
     * Create a new instance.
     *
     * @param string|null $time
     * @param \DateTimeZone|string|null $tz
     */
    public function __construct($time = null, $tz = null)
    {
        parent::__construct($time, new DateTimeZone('UTC'));

        $this->startOfDay();
    }

    /**
     * Prevent the time from being changed.
     *
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @param int $microseconds
     * @return static
     */
    public function setTime($hours, $minutes, $seconds = null, $microseconds = null)
    {
        return $this;
    }

    /**
     * Add given units or interval to the current instance.
     *
     * @example $date->add('hour', 3)
     * @example $date->add(15, 'days')
     * @example $date->add(CarbonInterval::days(4))
     *
     * @param string|DateInterval $unit
     * @param int $value
     * @param bool|null $overflow
     * @return \Carbon\CarbonInterface
     */
    public function add($unit, $value = 1, $overflow = null)
    {
        return parent::add($unit, $value, $overflow)->startOfDay();
    }

    /**
     * Subtract given units or interval to the current instance.
     *
     * @example $date->sub('hour', 3)
     * @example $date->sub(15, 'days')
     * @example $date->sub(CarbonInterval::days(4))
     *
     * @param string|DateInterval $unit
     * @param int $value
     * @param bool|null $overflow
     * @return \Carbon\CarbonInterface
     */
    public function sub($unit, $value = 1, $overflow = null)
    {
        return parent::sub($unit, $value, $overflow)->startOfDay();
    }

    /**
     * Prevent the timezone from being changed.
     *
     * @param \DateTimeZone|string $timezone
     * @return static
     */
    public function setTimezone($timezone)
    {
        return $this;
    }

    /**
     * Set the timestamp without the time.
     *
     * @param int $value
     * @return static
     */
    public function setTimestamp($value)
    {
        return parent::setTimestamp($value)->startOfDay();
    }

    /**
     * Resets the time to 00:00:00 start of day
     *
     * @return static
     */
    public function startOfDay()
    {
        return parent::setTime(0, 0, 0);
    }

    /**
     * Ignore time changes.
     *
     * @param string $relative
     * @return static
     */
    public function modify($relative)
    {
        if (preg_match('/hour|minute|second/', $relative)) {
            return $this;
        }

        $new = parent::modify($relative);

        if ($new->format('H:i:s') !== '00:00:00') {
            return $new->startOfDay();
        }

        return $new;
    }
}
