<?php

namespace Ds\Common\Faker\PhoneNumbers;

use Faker\Provider\Base;
use Illuminate\Support\Arr;

class CanadaPhoneNumberProvider extends Base
{
    public static $formats = [];

    // Matching library used under the hood to validate phone numbers
    // Canadia regex at PhoneNumberMetadata_CA.php
    private $areas = [
        204, 226, 236, 249, 289, 250,
        306, 343, 365, 367,
        403, 416, 418, 431, 437, 438, 450,
        506, 514, 519, 548, 579, 581, 587,
        604, 613, 639, 647, 672,
        705, 709, 778, 780, 782,
        807, 867, 819, 825, 873,
        902, 905,
    ];

    public function canadianPhoneNumber(): string
    {
        return static::numerify($this->generator->parse(static::randomElement($this->getFormats())));
    }

    protected function getFormats(): array
    {
        return Arr::flatten(array_map(function ($area) {
            return array_map(function ($subArea) use ($area) {
                return [
                    // same validation for area and subarea
                    $this->getFormatsFromAreas($area, $subArea),
                    $this->getFormatsFromAreas($area, $subArea, ' x###'),
                    $this->getFormatsFromAreas($area, $subArea, ' x####'),
                    $this->getFormatsFromAreas($area, $subArea, ' x#####'),
                ];
            }, $this->areas);
        }, $this->areas));
    }

    protected function getFormatsFromAreas(string $area, string $subArea, ?string $suffix = ''): array
    {
        return [
            "$area-$subArea-####$suffix",
            "$area.$subArea.####$suffix",
            "$area $subArea ####$suffix",
            "($area) $subArea-####$suffix",
            "1-$area-$subArea-####$suffix",
            "1 ($area) $subArea-####$suffix",
            "+1 ($area) $subArea-####$suffix",
        ];
    }
}
