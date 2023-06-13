<?php

namespace Tests\Fakes;

use Ds\Domain\MissionControl\ShortlinkService;

class FakeShortlinkService extends ShortlinkService
{
    public function make($permalink, $linkable = null)
    {
        return 'https://gcld.co/shrt-lnk';
    }
}
