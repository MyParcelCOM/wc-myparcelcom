<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\Interfaces\StatusInterface;
use MyParcelCom\ApiSdk\Resources\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $status = new Status();
        $this->assertEquals('status-id', $status->setId('status-id')->getId());
    }

    /** @test */
    public function testType()
    {
        $status = new Status();
        $this->assertEquals('statuses', $status->getType());
    }

    /** @test */
    public function testCode()
    {
        $status = new Status();
        $this->assertEquals('shipment_concept', $status->setCode('shipment_concept')->getCode());
    }

    /** @test */
    public function testDescription()
    {
        $status = new Status();
        $this->assertEquals('The shipment is created', $status->setDescription('The shipment is created')->getDescription());
    }

    /** @test */
    public function testLevel()
    {
        $status = new Status();
        $this->assertEquals(StatusInterface::LEVEL_FAILED, $status->setLevel(StatusInterface::LEVEL_FAILED)->getLevel());
    }

    /** @test */
    public function testName()
    {
        $status = new Status();
        $this->assertEquals('Delivered', $status->setName('Delivered')->getName());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $status = (new Status())
            ->setDescription('The shipment is created')
            ->setCode('shipment_concept')
            ->setId('status-id')
            ->setName('Delivered')
            ->setLevel(StatusInterface::LEVEL_FAILED);

        $this->assertEquals([
            'id'         => 'status-id',
            'type'       => 'statuses',
            'attributes' => [
                'description' => 'The shipment is created',
                'code'        => 'shipment_concept',
                'level'       => 'failed',
                'name'        => 'Delivered',
            ],
        ], $status->jsonSerialize());
    }
}
