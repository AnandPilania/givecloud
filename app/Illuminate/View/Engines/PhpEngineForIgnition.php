<?php

namespace Ds\Illuminate\View\Engines;

use Ds\Illuminate\View\Concerns\IgnoreErrorNoticesDuringPathEvaluation;
use Facade\Ignition\Views\Engines\PhpEngine as BasePhpEngine;

class PhpEngineForIgnition extends BasePhpEngine
{
    use IgnoreErrorNoticesDuringPathEvaluation;
}
