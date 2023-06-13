<?php

namespace Tests;

use Database\Stories\FundraisingFormContributionStory;
use Database\Stories\FundraisingPageStory;
use Database\Stories\OnetimeContributionStory;
use Database\Stories\ProductStory;
use Database\Stories\RecurringContributionStory;
use Database\Stories\SupporterStory;

class StoryBuilder
{
    public static function fundraisingFormContribution(...$parameters): FundraisingFormContributionStory
    {
        return FundraisingFormContributionStory::factory(...$parameters);
    }

    public static function fundraisingPage(...$parameters): FundraisingPageStory
    {
        return FundraisingPageStory::factory(...$parameters);
    }

    public static function product(...$parameters): ProductStory
    {
        return ProductStory::factory(...$parameters);
    }

    public static function onetimeContribution(...$parameters): OnetimeContributionStory
    {
        return OnetimeContributionStory::factory(...$parameters);
    }

    public static function recurringContribution(...$parameters): RecurringContributionStory
    {
        return RecurringContributionStory::factory(...$parameters);
    }

    public static function supporter(...$parameters): SupporterStory
    {
        return SupporterStory::factory(...$parameters);
    }
}
