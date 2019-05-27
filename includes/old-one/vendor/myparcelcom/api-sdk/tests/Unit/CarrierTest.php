<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\Carrier;
use PHPUnit\Framework\TestCase;

class CarrierTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $carrier = new Carrier();

        $this->assertEquals('carrier-id', $carrier->setId('carrier-id')->getId());
    }

    /** @test */
    public function testName()
    {
        $carrier = new Carrier();

        $this->assertEquals('MyParcel.com Carrier', $carrier->setName('MyParcel.com Carrier')->getName());
    }

    /** @test */
    public function testGetType()
    {
        $carrier = new Carrier();

        $this->assertEquals('carriers', $carrier->getType());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $carrier = (new Carrier())
            ->setId('carrier-id')
            ->setName('MyParcel.com Carrier');

        $this->assertEquals([
            'id'         => 'carrier-id',
            'type'       => 'carriers',
            'attributes' => [
                'name' => 'MyParcel.com Carrier',
            ],
        ], $carrier->jsonSerialize());
    }
}
