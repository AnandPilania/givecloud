<?php

namespace Tests\Unit\Domain\Theming\Liquid;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class DropTest extends TestCase
{
    public function testResolveViewErrorBagToDefaultMessageBag(): void
    {
        $messageBag = new MessageBag([
            'first_name' => 'First name is required.',
            'email' => 'Email must be unique',
        ]);

        $data = Drop::resolveData((new ViewErrorBag)->put('default', $messageBag));

        $this->assertEquals($messageBag->toArray(), $data);
    }
}
