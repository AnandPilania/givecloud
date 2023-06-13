<?php

namespace Ds\Services;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class GoogleVisionService
{
    /**
     * Run label detection for an image.
     *
     * @param string $url
     * @return array
     */
    public function getImageLabels(string $url): array
    {
        $imageAnnotator = new ImageAnnotatorClient([
            'credentials' => config('services.google-storage.key_file'),
        ]);
        $response = $imageAnnotator->labelDetection($url);
        $labelCollection = $response->getLabelAnnotations();
        $labels = [];
        foreach ($labelCollection as $label) {
            $labels[] = $label->getDescription();
        }
        $imageAnnotator->close();

        return $labels;
    }
}
