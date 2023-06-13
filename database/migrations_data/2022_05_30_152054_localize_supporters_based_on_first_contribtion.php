<?php

use Ds\Illuminate\Console\ProgressBar;
use Ds\Models\Member;
use Ds\Services\LocaleService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Migrations\Migration;

class LocalizeSupportersBasedOnFirstContribtion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $locale = app(LocaleService::class)->siteLocale();

        $memberQuery = Member::query()
            ->whereHas('firstOrder', function (Builder $query) {
                $query->where('is_pos', false)
                    ->where('created_by', '!=', config('givecloud.super_user_id'));
            })->with(['firstOrder' => function (HasOne $query) {
                $query->where('is_pos', false)
                    ->where('created_by', '!=', config('givecloud.super_user_id'));
            }]);

        $progress = new ProgressBar($memberQuery->count());
        $progress->start();

        $memberQuery
            ->lazy(500)
            ->each(function (Member $member) use ($locale, $progress) {
                if ($member->firstOrder->currency_code) {
                    $member->currency_code ??= $member->firstOrder->currency_code;
                }

                if ($member->firstOrder->client_ip) {
                    $member->timezone ??= app('geoip')->get('timezone', $member->firstOrder->client_ip);
                }

                $member->language ??= $member->firstOrder->language ?? $locale;

                if ($member->isDirty()) {
                    $member->saveQuietly();
                }

                $progress->advance();
            });

        $progress->finish();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Member::query()->update([
            'language' => null,
            'timezone' => null,
            'currency_code' => null,
        ]);
    }
}
