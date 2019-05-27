<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Collection\ArrayCollection;
use MyParcelCom\ApiSdk\Collection\CollectionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use PHPUnit\Framework\TestCase;

class ArrayCollectionTest extends TestCase
{
    /** @var array */
    protected $resourcesArray = [];

    /** @var CollectionInterface */
    protected $collection;

    protected function setUp()
    {
        parent::setUp();

        for ($i = 0; $i < 50; $i++) {
            $resource = $this->createMock(ResourceInterface::class);
            $resource->id = $i;

            $this->resourcesArray[] = $resource;
        }

        $this->collection = new ArrayCollection($this->resourcesArray);
    }

    /** @test */
    public function testGet()
    {
        $resources = $this->collection->offset(20)->limit(5)->get();
        $this->assertCount(5, $resources);
        $this->assertEquals(24, $resources[24]->id);
    }

    /** @test */
    public function testForeach()
    {
        foreach ($this->collection as $resource) {
            $this->assertGreaterThanOrEqual(0, $resource->id);
            $this->assertLessThanOrEqual(29, $resource->id);
        }
    }

    /** @test */
    public function testOffsetAndLimit()
    {
        $resources = $this->collection->offset(15)->limit(20)->get();
        $this->assertCount(20, $resources);

        array_walk($resources, function ($resource) {
            $this->assertGreaterThanOrEqual(15, $resource->id);
            $this->assertLessThanOrEqual(35, $resource->id);
        });
    }

    /** @test */
    public function testCount()
    {
        $this->assertEquals(50, $this->collection->count());
    }

    /** @test */
    public function testValid()
    {
        $this->assertTrue($this->collection->offset(35)->valid());
        $this->assertFalse($this->collection->offset(1512312)->valid());
    }

    /** @test */
    public function testKeyCurrentAndNext()
    {
        $this->assertEquals(48, $this->collection->offset(48)->key());

        $this->collection->next();
        $this->assertEquals(49, $this->collection->current()->id);

        $this->assertNull($this->collection->offset(123541243)->current());
    }
}
