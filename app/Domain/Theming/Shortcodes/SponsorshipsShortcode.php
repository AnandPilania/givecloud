<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Illuminate\Support\Facades\DB;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class SponsorshipsShortcode extends Shortcode
{
    /**
     * Output the sponsorships template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $style = $s->getParameter('style', '');
        $references = $s->getParameter('references', '');
        $filter = $s->getParameter('filter', '');
        $maxAge = $s->getParameter('max-age', '');
        $orderby = $s->getParameter('orderby', 'reference');
        $order = $s->getParameter('order', 'desc');
        $limit = $s->getParameter('limit', '6');

        if (! $style) {
            return $this->error("'style' attribute required");
        }

        // only allow orderby: published_at or sequence
        if (! in_array($orderby, ['reference', 'random'])) {
            $orderby = 'reference';
        }

        // normalize options with db columns
        if ($orderby == 'reference') {
            $orderby = 'reference_number';
        }

        // only allow order: asc or desc
        if (! in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }

        // only allow orderby: published_at or sequence
        if (! in_array($filter, ['birthday-this-month', 'birthday-this-week', 'birthday-same-as-account', 'waiting-longest'])) {
            $filter = null;
        }

        // normalize published_at to database column name
        if ($orderby === 'published_at') {
            $orderby = 'postdatetime';
        }

        // cast the limit
        $limit = $limit ? min((int) $limit, 25) : 25;

        // codes array
        $references = ($references) ? explode(',', $references) : [];

        $sponsorships = \Ds\Domain\Sponsorship\Models\Sponsorship::active()
            ->where('is_sponsored', 0);

        if ($maxAge) {
            $sponsorships->whereRaw("(DATE_FORMAT(FROM_DAYS(DATEDIFF(?,birth_date)), '%Y')+0) <= ?", [
                fromLocal('now')->format('Y-m-d'), $maxAge,
            ]);
        }

        if ($orderby == 'random') {
            $sponsorships->orderBy(DB::raw('RAND()'));
        } else {
            $sponsorships->orderBy($orderby, $order);
        }

        if (count($references) > 0) {
            $sponsorships->whereIn('reference_number', $references);
        }

        if ($limit) {
            $sponsorships->take($limit);
        }

        $template = rtrim("templates/shortcodes/sponsorships.$style", '.');
        $template = new \Ds\Domain\Theming\Liquid\Template($template);

        return $template->render([
            'id' => uniqid('sponsorships-marquee-'),
            'sponsorees' => $sponsorships->get(),
        ]);
    }
}
