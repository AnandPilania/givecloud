<?php

namespace Tests\Unit\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drops\LinkListDrop;
use Ds\Domain\Theming\Liquid\Drops\LinkListsDrop;
use Ds\Models\Node;
use Illuminate\Support\Str;
use Tests\TestCase;

class LinkListsDropTest extends TestCase
{
    public function testLookupUsingId(): void
    {
        $menu = Node::factory()->navNenu()->create();

        $menuDrop = (new LinkListsDrop)->invokeDrop($menu->id);

        $this->assertInstanceOf(LinkListDrop::class, $menuDrop);
        $this->assertEquals($menu->id, $menuDrop->getSource()->id);
    }

    public function testLookupUsingHandle(): void
    {
        $menu = Node::factory()->navNenu()->create();

        $menuDrop = (new LinkListsDrop)->invokeDrop(Str::slug($menu->title));

        $this->assertInstanceOf(LinkListDrop::class, $menuDrop);
        $this->assertEquals($menu->id, $menuDrop->getSource()->id);
    }
}
