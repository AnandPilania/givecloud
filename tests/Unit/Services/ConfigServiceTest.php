<?php

namespace Tests\Unit\Services;

use Ds\Services\ConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConfigServiceTest extends TestCase
{
    public function testGettingAConfigAfterItsBeenSet()
    {
        $configService = $this->app->make(ConfigService::class);

        $this->assertTrue($configService->set('test_setting_value', 'working'));
        $this->assertSame('working', $configService->get('test_setting_value'));
    }

    public function testGetValueMatchesSetFromRequest()
    {
        config(['sys.defaults.test_setting_config_from_request' => '0']);

        $configService = $this->app->make(ConfigService::class);

        $this->app->instance(
            'request',
            new Request([], ['test_setting_config_from_request' => 1])
        );

        $this->assertTrue($configService->setFromRequest());
        $this->assertSame($configService->get('test_setting_config_from_request'), '1');
    }

    public function testDbInsertFailWhileSettingConfig()
    {
        DB::shouldReceive('table')->once()
            ->with('configs')
            ->andReturnSelf();

        DB::shouldReceive('insert')->once()
            ->andReturnFalse();

        $this->assertFalse(sys_set('test_db_insert_fail_while_setting_config', true));
    }
}
