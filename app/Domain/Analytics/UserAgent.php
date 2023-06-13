<?php

namespace Ds\Domain\Analytics;

use Ds\Common\DataAccess;
use Illuminate\Support\Traits\ForwardsCalls;
use Jenssegers\Agent\Agent;
use UAParser\DeviceParser;
use UAParser\Parser;

/**
 * @property string $type;
 * @property string $browserName;
 * @property string $browserVersion;
 * @property string $platformName;
 * @property string $platformVersion;
 * @property string $deviceName;
 * @property string $deviceBrand;
 * @property string $botName;
 * @property string $userAgentString;
 */
class UserAgent extends DataAccess
{
    use ForwardsCalls;

    /** @var \Jenssegers\Agent\Agent */
    private $agent;

    public function __construct(?string $userAgentString = null)
    {
        $this->setUserAgentString($userAgentString);
    }

    public static function make(string $userAgentString = null): self
    {
        return new static($userAgentString ?? request()->userAgent());
    }

    private function setUserAgentString(?string $userAgentString): void
    {
        $this->agent = $agent = new Agent(null, $userAgentString);

        $result = Parser::create()->parse((string) $userAgentString);
        $device = DeviceParser::create()->parseDevice((string) $userAgentString);

        $configTypeBot = $agent->isRobot() ? 'bot' : null;
        $configTypeTablet = $agent->isTablet() ? 'tablet' : null;
        $configTypeMobile = $agent->isMobile() ? 'mobile' : null;

        $this->fill([
            'type' => $configTypeBot ?? $configTypeTablet ?? $configTypeMobile ?? 'desktop',
            'browserName' => $agent->browser() ?: $result->ua->family ?: null,
            'browserVersion' => $agent->version($this->browser) ?: $result->ua->toVersion() ?: null,
            'platformName' => $agent->platform() ?: $result->os->family ?: null,
            'platformVersion' => str_replace('_', '.', $agent->version($this->platform)) ?: $result->os->toVersion() ?: null,
            'deviceName' => $device->model ?: $agent->device() ?: null,
            'deviceBrand' => $device->brand ?: null,
            'botName' => $agent->robot() ?: null,
            'userAgentString' => $userAgentString,
        ]);
    }

    public function isLatestAndroidOS(): bool
    {
        return  $this->platformName === 'AndroidOS' && version_compare('12', $this->platformVersion) <= 0;
    }

    public function isLatestIOS(): bool
    {
        return  $this->platformName === 'iOS' && version_compare('16', $this->platformVersion) <= 0;
    }

    public function isLatestOSX(): bool
    {
        return  $this->platformName === 'OS X' && version_compare('10.15', $this->platformVersion) <= 0;
    }

    public function isLatestWindows(): bool
    {
        return  $this->platformName === 'Windows' && version_compare('11', $this->platformVersion) <= 0;
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->agent, $method, $parameters);
    }
}
