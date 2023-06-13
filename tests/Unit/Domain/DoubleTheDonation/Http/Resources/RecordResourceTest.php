<?php

namespace Tests\Unit\Domain\DoubleTheDonation\Http\Resources;

use Ds\Domain\DoubleTheDonation\Http\Resources\RecordResource;
use Tests\TestCase;

/** @group DoubleTheDonation */
class RecordResourceTest extends TestCase
{
    public function testResourceToArrayMergesStatusLabel(): void
    {
        $resource = ['status' => ':pending-payment'];

        $this->assertSame(
            [
                'status' => ':pending-payment',
                'status_label' => 'Pending Payment',
            ],
            RecordResource::make($resource)->toArray(request()),
        );
    }
}
