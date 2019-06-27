<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\CarrierStatus;
use PHPUnit\Framework\TestCase;

class CarrierStatusTest extends TestCase
{
    /** @test */
    public function testAccessors()
    {
        $carrierStatus = new CarrierStatus();

        $this->assertEquals('4w350m3', $carrierStatus->setCode('4w350m3')->getCode());
        $this->assertEquals('This is a very helpful description', $carrierStatus->setDescription('This is a very helpful description')->getDescription());
        $this->assertEquals(13371337, $carrierStatus->setAssignedAt(13371337)->getAssignedAt()->getTimestamp());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $carrierStatus = (new CarrierStatus())
            ->setCode('4w350m3')
            ->setDescription('This is a very helpful description')
            ->setAssignedAt(13371337);

        $this->assertEquals(
            [
                'code'        => '4w350m3',
                'description' => 'This is a very helpful description',
                'assigned_at' => 13371337,
            ],
            $carrierStatus->jsonSerialize()
        );
    }
}
