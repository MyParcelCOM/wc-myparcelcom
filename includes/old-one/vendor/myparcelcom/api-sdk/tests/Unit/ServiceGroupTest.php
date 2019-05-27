<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\ServiceGroup;
use PHPUnit\Framework\TestCase;

class ServiceGroupTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $serviceGroup = new ServiceGroup();
        $this->assertEquals('service-group-id', $serviceGroup->setId('service-group-id')->getId());
    }

    /** @test */
    public function testGetType()
    {
        $serviceGroup = new ServiceGroup();
        $this->assertEquals('service-groups', $serviceGroup->getType());
    }

    /** @test */
    public function testWeightMin()
    {
        $serviceGroup = new ServiceGroup();
        $this->assertEquals(123, $serviceGroup->setWeightMin(123)->getWeightMin());
    }

    /** @test */
    public function testWeightMax()
    {
        $serviceGroup = new ServiceGroup();
        $this->assertEquals(987, $serviceGroup->setWeightMax(987)->getWeightMax());
    }

    /** @test */
    public function testCurrency()
    {
        $serviceGroup = new ServiceGroup();
        $this->assertEquals('GBP', $serviceGroup->setCurrency('GBP')->getCurrency());
    }

    /** @test */
    public function testPrice()
    {
        $serviceGroup = new ServiceGroup();
        $this->assertEquals(741, $serviceGroup->setPrice(741)->getPrice());
    }

    /** @test */
    public function testStepSize()
    {
        $serviceGroup = new ServiceGroup();
        $this->assertEquals(100, $serviceGroup->setStepSize(100)->getStepSize());
    }

    /** @test */
    public function testStepPrice()
    {
        $serviceGroup = new ServiceGroup();
        $this->assertEquals(10, $serviceGroup->setStepPrice(10)->getStepPrice());
    }

    public function testJsonSerialize()
    {
        $serviceGroup = (new ServiceGroup())
            ->setId('service-group-id')
            ->setWeightMin(123)
            ->setWeightMax(987)
            ->setCurrency('GBP')
            ->setPrice(741)
            ->setStepSize(10)
            ->setStepPrice(10);

        $this->assertEquals([
            'id'         => 'service-group-id',
            'type'       => 'service-groups',
            'attributes' => [
                'price'      => [
                    'amount'   => 741,
                    'currency' => 'GBP',
                ],
                'step_price' => [
                    'amount'   => 10,
                    'currency' => 'GBP',
                ],
                'step_size'  => 10,
                'weight'     => [
                    'max' => 987,
                    'min' => 123,
                ],
            ],
        ], $serviceGroup->jsonSerialize());
    }
}
