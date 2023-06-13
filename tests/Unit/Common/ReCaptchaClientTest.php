<?php

namespace Tests\Unit\Common;

use Ds\Common\ReCaptchaClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReCaptchaClientTest extends TestCase
{
    public function testHandlingSuccessfulVerification(): void
    {
        $this->assertTrue($this->runReCaptchaVerification([
            'success' => true,
            'hostname' => site()->subdomain,
        ]));
    }

    public function testHandlingFailedVerification(): void
    {
        $this->assertFalse($this->runReCaptchaVerification([
            'success' => false,
            'hostname' => site()->subdomain,
        ]));
    }

    /*
    public function testHandlingFailedHostnameValidation(): void
    {
        $this->assertFalse($this->runReCaptchaVerification([
            'success' => true,
            'hostname' => null,
        ]));
    }
    */

    public function testHandlingSuccessfulManualTokenVerification(): void
    {
        $recaptcha = $this->getReCaptchaClient();

        request()->merge([
            ReCaptchaClient::VERIFY_REQUEST_KEY => $recaptcha->generateVerificationToken(),
        ]);

        $this->assertTrue($recaptcha->verify());
    }

    private function runReCaptchaVerification(array $data): bool
    {
        Http::fake([
            'google.com/recaptcha/*' => Http::response($data),
        ]);

        request()->merge([
            ReCaptchaClient::VERIFY_REQUEST_KEY => Str::random(40),
        ]);

        return $this->getReCaptchaClient()->verify();
    }

    private function getReCaptchaClient(): ReCaptchaClient
    {
        return $this->app->make(ReCaptchaClient::class, [
            'siteKey' => Str::random(40),
            'secretKey' => Str::random(40),
        ]);
    }
}
