<?php

namespace Ds\Illuminate\Cache;

use DateTimeInterface;
use Ds\Domain\Shared\DateTime;

class CacheEntity
{
    /** @var \Ds\Domain\Shared\DateTime */
    private $expiryDate;

    /** @var mixed */
    public $data;

    /**
     * Create an instance.
     *
     * @param \DateTimeInterface $expiryDate
     * @param mixed $data
     */
    public function __construct(DateTimeInterface $expiryDate, $data)
    {
        $this->expiryDate = DateTime::instance($expiryDate);
        $this->data = $data;
    }

    /**
     * Has the data expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiryDate->isPast();
    }
}
