<?php

namespace Ds\Illuminate\View\Concerns;

trait IgnoreErrorNoticesDuringPathEvaluation
{
    /**
     * Get the evaluated contents of the view at the given path.
     *
     * @param string $path
     * @param array $data
     * @return string
     */
    protected function evaluatePath($path, $data)
    {
        $reportingLevel = error_reporting();

        // Most of our old views/templates don't do any
        // checks for existance of properties/indexes/keys/etc
        error_reporting(E_ALL ^ E_NOTICE);

        $content = parent::evaluatePath($path, $data);

        error_reporting($reportingLevel);

        return $content;
    }
}
