<?php

namespace Tests\Unit\Helpers;

use Ds\Services\ConfigService;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    public function testSysGetReturnsConfigServiceInstance()
    {
        $this->assertSame(sys_get(), ConfigService::getInstance());
    }

    public function testGettingAConfigAfterItsBeenSet()
    {
        $this->assertTrue(sys_set('test_setting_config', 'works'));
        $this->assertEquals(sys_get('test_setting_config'), 'works');
    }
}
