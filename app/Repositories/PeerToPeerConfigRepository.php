<?php

namespace Ds\Repositories;

use Ds\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class PeerToPeerConfigRepository
{
    /** @var \Ds\Models\Product */
    private $product;

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getConfig(): array
    {
        if (empty($this->product)) {
            throw new ModelNotFoundException;
        }

        $supporter = member();

        return [
            'fundraising_experience' => $this->getFundraisingExperience(),
            'supporter' => optional($supporter)->toLiquid(),
        ];
    }

    private function getFundraisingExperience(): array
    {
        $config = app(DonationFormConfigRepository::class)
            ->setProduct($this->product)
            ->getConfig();

        return Arr::only($config, [
            'id',
            'logo_url',
            'background_url',
            'landing_page_headline',
            'landing_page_description',
            'social_preview_image',
            'primary_colour',
            'page_title',
            'page_description',
            'payment_provider_website_url',
            'local_country',
            'local_currency',
            'local_geolocation',
            'global_settings',
            'transparency_promise',
            'accounts_login_url',
        ]);
    }
}
