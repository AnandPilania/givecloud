<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Illuminate\Support\Str;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class AccountGivingShortcode extends Shortcode
{
    /**
     * Output account giving.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $atts = [
            'start_date' => (string) $s->getParameter('start_date', null),
            'end_date' => (string) $s->getParameter('end_date', null),
            'format' => (string) $s->getParameter('format', '$0,0.00'),
            'exclude_donations' => Str::boolify((string) $s->getParameter('exclude_donations', null)),
            'exclude_purchases' => Str::boolify((string) $s->getParameter('exclude_purchases', null)),
            'exclude_fundraising' => Str::boolify((string) $s->getParameter('exclude_fundraising', null)),
        ];

        if (! member()) {
            return money(0)->format($atts['format']);
        }

        $total = 0;
        $opts = [
            'start_date' => $atts['start_date'],
            'end_date' => $atts['end_date'],
        ];
        $totals = reqcache('account-giving:' . sha1(serialize($opts)), function () use ($opts) {
            return member()->calculateGivingTotals($opts);
        });

        if (! $atts['exclude_donations']) {
            $total += $totals['donation_amount'];
        }

        if (! $atts['exclude_purchases']) {
            $total += $totals['purchase_amount'];
        }

        if (! $atts['exclude_fundraising']) {
            $total += $totals['fundraising_amount'];
        }

        return money($total)->format($atts['format']);
    }
}
