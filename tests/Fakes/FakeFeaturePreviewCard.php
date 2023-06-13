<?php

namespace Tests\Fakes;

use Ds\Domain\FeaturePreviews\PreviewCards\AbstractPreviewCard;

class FakeFeaturePreviewCard extends AbstractPreviewCard
{
    public function title(): string
    {
        return 'A title';
    }

    public function description(): string
    {
        return 'Lorem ipsum';
    }

    public function links(): array
    {
        return [
            'A link' => 'https://google.ca',
        ];
    }
}
