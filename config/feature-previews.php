<?php

/*
 * Returns all Features for previews.
 * Cards must extends Ds\Domain\FeaturePreviews\PreviewCards\AbstractPerviewCard
 * and implement Ds\Domain\FeaturePreviews\PreviewCards\PreviewCardInterface
 */

use Ds\Domain\FeaturePreviews\PreviewCards\QuickStartMenu;
use Ds\Domain\FeaturePreviews\PreviewCards\UI2021Feature;

return [
    UI2021Feature::class,
    QuickStartMenu::class,
];
