<?php

namespace Tests\Unit\Models;

use Ds\Models\ProductCustomField;
use Tests\TestCase;

class ProductCustomFieldTest extends TestCase
{
    public function testGetChoicesAttributeWithEmptyOptions(): void
    {
        $productCustomFieldModel = ProductCustomField::factory()->make();

        $this->assertIsArray($productCustomFieldModel->choices);
        $this->assertEmpty($productCustomFieldModel->choices);
    }

    public function testGetChoicesAttributeWithJsonOptions(): void
    {
        $productCustomFieldModel = ProductCustomField::factory()->jsonOptions()->make();

        $this->assertIsArray($productCustomFieldModel->choices);
        $this->assertCount(count(json_decode($productCustomFieldModel->options)), $productCustomFieldModel->choices);

        $firstChoice = $productCustomFieldModel->choices[0];
        $this->assertIsObject($firstChoice);
        $this->assertObjectHasAttribute('label', $firstChoice);
        $this->assertObjectHasAttribute('value', $firstChoice);
    }

    public function testGetChoicesAttributeWithSimpleOptions(): void
    {
        $productCustomFieldModel = ProductCustomField::factory()->simpleOptions()->make([
            'options' => "test\r\ntest-2\ntest-3", // test with both CRLF and LF
        ]);

        $this->assertIsArray($productCustomFieldModel->choices);
        $this->assertCount(3, $productCustomFieldModel->choices);

        $firstChoice = $productCustomFieldModel->choices[0];
        $this->assertIsObject($firstChoice);
        $this->assertObjectHasAttribute('label', $firstChoice);
        $this->assertObjectHasAttribute('value', $firstChoice);
        $this->assertSame($firstChoice->label, $firstChoice->value);
    }
}
