<?php

namespace Ds\Domain\Sponsorship\Models;

use Database\Factories\MediaFactory;
use Ds\Illuminate\Database\Eloquent\AuthoritativeDatabase;
use Ds\Illuminate\Database\Eloquent\HasAuthoritativeDatabase;
use Ds\Models\Media;

class SponsorshipMedia extends Media implements AuthoritativeDatabase
{
    use HasAuthoritativeDatabase;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return 'media_id';
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public static function newFactory()
    {
        return MediaFactory::new();
    }

    /**
     * Attribute Mutator: Public URL
     *
     * @return string
     */
    public function getPublicUrlAttribute()
    {
        $site = site();

        if ($site->authoritative_site) {
            $site = $site->authoritative_site;
        }

        return "https://cdn.givecloud.co/{$site->cdn_path_prefix}/{$this->collection_name}/{$this->filename}";
    }
}
