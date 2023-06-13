<?php

namespace Tests\Unit\Http\Resources;

use Ds\Http\Resources\FundraisingPageResource;
use Ds\Models\FundraisingPage;
use Illuminate\Http\Request;
use Tests\TestCase;

class FundraisingPageResourceTest extends TestCase
{
    public function testFundraisingPageToArray(): void
    {
        $page = FundraisingPage::factory()->create();

        $result = (new FundraisingPageResource($page))->toArray(new Request());

        $this->assertArrayHasKey('id', $result);
        $this->assertSame($page->hashid, $result['id']);
        $this->assertSame($page->title, $result['title']);
        $this->assertSame($page->absolute_url, $result['url']);
        $this->assertSame($page->category, $result['category']);

        $this->assertSame($page->goal_deadline->toDateTimeString(), $result['goal_deadline']->toDateTimeString());

        $this->assertArrayHasKey('supporter', $result);
    }
}
