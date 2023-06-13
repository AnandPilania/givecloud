<?php

namespace Ds\Domain\MissionControl;

use Hashids\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShortlinkService
{
    /**
     * Retrieve an existing shortlink or create a new one.
     *
     * @param string $permalink
     * @param \Illuminate\Database\Eloquent\Model|string $linkable
     * @return string
     */
    public function make($permalink, $linkable = null)
    {
        if ($linkable instanceof Model) {
            $id = $this->getShortlinkForModel($permalink, $linkable);
        } elseif ($linkable) {
            $id = $this->getShortlinkForRouteName($permalink, $linkable);
        } else {
            $id = $this->getShortlinkForPermalink($permalink);
        }

        return 'https://gcld.co/' . $this->getHashids()->encode($id);
    }

    /**
     * Retrieve an existing shortlink or create a new one.
     *
     * @param string $permalink
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return int
     */
    private function getShortlinkForModel($permalink, Model $model)
    {
        $link = DB::connection('sys-backend')->table('shortlinks')
            ->where('site_id', site()->id)
            ->where('linkable_id', $model->getKey())
            ->where('linkable_type', $model->getMorphClass())
            ->first();

        if ($link) {
            return $link->id;
        }

        return DB::connection('sys-backend')->table('shortlinks')->insertGetId([
            'site_id' => site()->id,
            'permalink' => $permalink,
            'linkable_id' => $model->getKey(),
            'linkable_type' => $model->getMorphClass(),
            'created_at' => now()->toDatetimeFormat(),
            'updated_at' => now()->toDatetimeFormat(),
        ]);
    }

    /**
     * Retrieve an existing shortlink or create a new one.
     *
     * @param string $permalink
     * @param string $routeName
     * @return int
     */
    private function getShortlinkForRouteName($permalink, $routeName)
    {
        $link = DB::connection('sys-backend')->table('shortlinks')
            ->where('site_id', site()->id)
            ->where('route_name', $routeName)
            ->first();

        if ($link) {
            return $link->id;
        }

        return DB::connection('sys-backend')->table('shortlinks')->insertGetId([
            'site_id' => site()->id,
            'permalink' => $permalink,
            'route_name' => $routeName,
            'created_at' => now()->toDatetimeFormat(),
            'updated_at' => now()->toDatetimeFormat(),
        ]);
    }

    /**
     * Retrieve an existing shortlink or create a new one.
     *
     * @param string $permalink
     * @return int
     */
    private function getShortlinkForPermalink($permalink)
    {
        $link = DB::connection('sys-backend')->table('shortlinks')
            ->where('site_id', site()->id)
            ->where('permalink', $permalink)
            ->first();

        if ($link) {
            return $link->id;
        }

        return DB::connection('sys-backend')->table('shortlinks')->insertGetId([
            'site_id' => site()->id,
            'permalink' => $permalink,
            'created_at' => now()->toDatetimeFormat(),
            'updated_at' => now()->toDatetimeFormat(),
        ]);
    }

    /**
     * Get hashid specific to shortlink generation.
     *
     * @return \Hashids\Hashids
     */
    private function getHashids()
    {
        return new Hashids(
            'yLakZTE6tMjdnvFVaNMN5SbHyZQxMt9ckNyD1Qit',
            7,
            'abcdefghjklmnprstuvwxyzABCDEFGHJKLMNPRSTUVWXYZ23456789'
        );
    }
}
