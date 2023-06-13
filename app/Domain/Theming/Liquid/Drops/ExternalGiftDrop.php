<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use stdClass;

class ExternalGiftDrop extends Drop
{
    /** @var \stdClass */
    protected $source;

    /**
     * Create an instance.
     *
     * @param \stdClass $source
     */
    public function __construct(stdClass $source = null)
    {
        $this->source = $source;

        $this->initialize($source);
    }

    /**
     * @param \stdClass $source
     */
    protected function initialize($source)
    {
        $this->liquid = [
            'date' => fromLocal($source->gift_date),
            'currency' => $source->currency,
            'fair_market_value' => $source->fmv,
            'receipt_number' => $source->rcpt_num,
            'amount' => $source->amount,
            'reference' => $source->reference,
        ];
    }
}
