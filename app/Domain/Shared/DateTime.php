<?php

namespace Ds\Domain\Shared;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class DateTime extends Carbon
{
    /**
     * Create an instance from an integer, string or DateTime instance.
     *
     * @param mixed $time
     * @param \DateTimeZone|string|null $tz
     * @return static|\Ds\Domain\Shared\Date|null
     */
    public static function parseDateTime($time = null, $tz = null)
    {
        if (strtolower($tz) === 'local') {
            $tz = sys_get('timezone');
        }

        try {
            if ($time === null || $time === '' || is_bool($time)) {
                return null;
            }

            if (is_object($time)) {
                if ($time instanceof Date) {
                    return Date::instance($time);
                }

                if ($time instanceof \DateTime) {
                    return static::instance($time);
                }

                if ($time instanceof \DateTimeImmutable) {
                    return static::instance($time);
                }

                if ($time instanceof \stdClass && isset($time->date, $time->timezone)) {
                    return new static($time->date, $time->timezone);
                }

                return null;
            }

            $time = trim((string) $time);

            if ($time === '' || $time === '0000-00-00' || $time === '0000-00-00 00:00:00') {
                return null;
            }

            if (is_numeric($time)) {
                return static::createFromTimestamp((int) $time, $tz);
            }

            return new static($time, $tz);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Returns date formatted according to given format.
     *
     * @param string $format
     * @return string
     */
    public function format($format)
    {
        if (Str::startsWith($format, 'date:')) {
            return Date::instance($this)->format(Str::after($format, 'date:'));
        }

        $formatMethod = 'to' . Str:: studly($format) . 'Format';
        if (method_exists($this, $formatMethod)) {
            return $this->{$formatMethod}();
        }

        if (Str::contains($format, '%')) {
            return $this->formatLocalized($format);
        }

        return $this->translatedFormat($format);
    }

    public function untranslatedFormat(string $format): string
    {
        return parent::format($format);
    }

    public function toAutoFormat(): string
    {
        if ($this->isToday()) {
            return 'Today';
        }

        if ($this->isYesterday()) {
            return 'Yesterday';
        }

        if ($this->isTomorrow()) {
            return 'Tomorrow';
        }

        if ($this->isCurrentYear()) {
            return $this->translatedFormat('M j');
        }

        return $this->translatedFormat('M j, Y');
    }

    public function toAutoDiffFormat(): string
    {
        if ($this->isToday()) {
            return $this->diffForHumans();
        }

        return $this->toAutoFormat();
    }

    public function toApiFormat(): string
    {
        if ($this instanceof Date) {
            return $this->translatedFormat('Y-m-d');
        }

        if ($this->getOffset() === 0) {
            return $this->translatedFormat('Y-m-d\TH:i:s\Z');
        }

        return $this->translatedFormat('Y-m-d\TH:i:sO');
    }

    public function toCsvFormat(): string
    {
        if ($this instanceof Date) {
            return $this->translatedFormat('d-M-y');
        }

        return $this->translatedFormat('d-M-y g:i A');
    }

    public function toDateFormat(): string
    {
        return $this->translatedFormat('Y-m-d');
    }

    public function toDatetimeFormat(): string
    {
        return $this->translatedFormat('Y-m-d H:i:s');
    }

    public function toDiffDaysFormat(): string
    {
        $days = $this->diffInDays(null);

        return $days . ' ' . Str::plural('day', $days);
    }

    public function toFdateFormat(): string
    {
        return $this->translatedFormat('M j, Y');
    }

    public function toFdatetimeFormat(): string
    {
        return $this->translatedFormat('M j, Y h:ia');
    }

    public function toHumansFormat(): string
    {
        return $this->diffForHumans();
    }

    public function toHumansShortFormat(): string
    {
        return $this->diffForHumans(null, static::DIFF_RELATIVE_AUTO, true);
    }

    public function toJsonFormat(): string
    {
        if ($this instanceof Date) {
            return $this->translatedFormat('Y-m-d');
        }

        return $this->rawFormat('Y-m-d H:i:s e');
    }

    /**
     * Convert to Date.
     *
     * @return \Ds\Domain\Shared\Date
     */
    public function asDate()
    {
        return Date::instance($this);
    }

    /**
     * Convert to local timezone without changing the time.
     *
     * @return static
     */
    public function asLocal()
    {
        return new static($this->toDatetimeFormat(), sys_get('timezone'));
    }

    /**
     * Convert to local timezone.
     *
     * @return static
     */
    public function toLocal()
    {
        return static::instance($this)->setTimezone(sys_get('timezone'));
    }

    /**
     * Convert to UTC timezone without changing the time.
     *
     * @return static
     */
    public function asUtc()
    {
        return new static($this->toDatetimeFormat(), 'UTC');
    }

    /**
     * Convert to UTC timezone.
     *
     * @return static
     */
    public function toUtc()
    {
        return static::instance($this)->setTimezone('UTC');
    }
}
