<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class AccountExternalDrop extends Drop
{
    protected $serializationBlacklist = [
        'gifts',
        'gifts_paged',
    ];

    public function gifts()
    {
        return $this->getGifts()->map(function ($gift) {
            return new ExternalGiftDrop($gift);
        });
    }

    public function gifts_paged()
    {
        $gifts = $this->getGifts(50);

        return new PaginateDrop($gifts, 'ExternalGift');
    }

    private function getGifts(int $resultsPerPage = 0)
    {
        $donor_id = $this->source->donor_id;

        if (empty($this->source->donor_id)) {
            return $this->getEmptyResultSet($resultsPerPage);
        }

        $page = LengthAwarePaginator::resolveCurrentPage('all_history_page');
        $cache_key = "external_gifts:{$donor_id}:pp{$resultsPerPage}-{$page}";
        $cache_seconds = now()->addMinutes(5); // 5 minutes

        return Cache::remember($cache_key, $cache_seconds, function () use ($donor_id, $resultsPerPage) {
            $start_date = sys_get('external_donations_start_date');
            $end_date = sys_get('external_donations_end_date');
            $gl_codes = sys_get('external_donations_gl_codes');
            $gift_types = sys_get('external_donations_gift_types');

            return app('Ds\Services\DonorPerfectService')->getGiftsByDonor($donor_id, $resultsPerPage, $start_date, $end_date, $gl_codes, $gift_types);
        });
    }

    private function getEmptyResultSet($resultsPerPage)
    {
        if ($resultsPerPage) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $resultsPerPage, 1, [
                'pageName' => 'all_history_page',
            ]);
        }

        return collect();
    }
}
