<?php

namespace Tests\Unit\Eloquent\Casts;

use Ds\Eloquent\Casts\AustinheapEncryption;
use Tests\TestCase;

class AustinheapEncryptionTest extends TestCase
{
    public function testBasicCasting(): void
    {
        $caster = new AustinheapEncryption;

        $inboundValue = 'Mary had a little lamb';
        $outboundValue = $caster->get(null, '', $caster->set(null, '', $inboundValue, []), []);

        $this->assertSame($inboundValue, $outboundValue);
    }

    public function testJsonCasting(): void
    {
        $caster = new AustinheapEncryption('json');

        $inboundValue = ['mary_had_a_little_lamb' => true];
        $outboundValue = $caster->get(null, '', $caster->set(null, '', $inboundValue, []), []);

        $this->assertIsArray($outboundValue);
        $this->assertArrayHasKey('mary_had_a_little_lamb', $outboundValue);
        $this->assertTrue($outboundValue['mary_had_a_little_lamb']);
    }

    public function testCastingUnencryptedValue(): void
    {
        $caster = new AustinheapEncryption;

        $inboundValue = 'Mary had a little lamb';
        $outboundValue = $caster->get(null, '', $inboundValue, []);

        $this->assertSame($inboundValue, $outboundValue);
    }
}
