<?php

namespace Tests\Unit\Repositories;

use Ds\Models\Node;
use Ds\Repositories\PageRepository;
use Tests\TestCase;

class PagesRepositoryTest extends TestCase
{
    /**
     * @dataProvider findingPageProvider
     */
    public function testFindingPage(string $methodName, string $attribute): void
    {
        $page = Node::factory()->create();

        $foundPage = $this->app->make(PageRepository::class)->{$methodName}($page->{$attribute});

        $this->assertSame($page->getKey(), $foundPage->getKey());
    }

    public function findingPageProvider(): array
    {
        return [
            ['find', 'id'],
            ['findByUrl', 'url'],
        ];
    }
}
