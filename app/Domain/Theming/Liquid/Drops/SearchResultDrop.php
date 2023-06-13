<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\FundraisingPage;
use Ds\Models\Node;
use Ds\Models\Post;
use Ds\Models\Product;

class SearchResultDrop extends Drop
{
    protected function initialize($source)
    {
        if ($source instanceof Product) {
            $this->liquid = [
                'type' => 'product',
                'rank' => 0,
                'title' => $source->name,
                'excerpt' => $source->summary,
                'feature_image' => $source->photo,
                'permalink' => $source->abs_url,
            ];
        } elseif ($source instanceof Node) {
            $this->liquid = [
                'type' => 'page',
                'rank' => 0,
                'title' => $source->title,
                'excerpt' => $source->metadescription,
                'feature_image' => $source->featuredImage,
                'permalink' => secure_site_url($source->abs_url),
            ];
        } elseif ($source instanceof Post) {
            $this->liquid = [
                'type' => 'post',
                'rank' => 0,
                'title' => $source->name,
                'excerpt' => $source->description,
                'feature_image' => $source->featuredImage,
                'permalink' => $source->absolute_url,
            ];
        } elseif ($source instanceof FundraisingPage) {
            $this->liquid = [
                'type' => 'fundraising_page',
                'rank' => 0,
                'title' => $source->title,
                'excerpt' => $source->description,
                'feature_image' => $source->photo,
                'permalink' => $source->absolute_url,
            ];
        } elseif ($source instanceof Sponsorship) {
            $this->liquid = [
                'type' => 'sponsoree',
                'rank' => 0,
                'title' => $source->first_name,
                'excerpt' => '',
                'feature_image' => $source->featuredImage,
                'permalink' => secure_site_url($source->url),
            ];
        }
    }
}
