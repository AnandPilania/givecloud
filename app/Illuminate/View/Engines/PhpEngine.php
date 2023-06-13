<?php

namespace Ds\Illuminate\View\Engines;

use Ds\Illuminate\View\Concerns\IgnoreErrorNoticesDuringPathEvaluation;
use Illuminate\View\Engines\PhpEngine as BasePhpEngine;

class PhpEngine extends BasePhpEngine
{
    use IgnoreErrorNoticesDuringPathEvaluation;
}
