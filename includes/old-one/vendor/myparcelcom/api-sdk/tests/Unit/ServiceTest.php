<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CServiceontractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\RegionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Service;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $service = new Service();
        $this->assertEquals('service-id', $service->setId('service-id')->getId());
    }

    /** @test */
    public function testType()
    {
        $service = new Service();
        $this->assertEquals('services', $service->getType());
    }

    /** @test */
    public function testName()
    {
        $service = new Service();
        $this->assertEquals('Easy Delivery Service', $service->setName('Easy Delivery Service')->getName());
    }

    /** @test */
    public function testPackageType()
    {
        $service = new Service();
        $this->assertEquals(Service::PACKAGE_TYPE_PARCEL, $service->setPackageType(Service::PACKAGE_TYPE_PARCEL)->getPackageType());
    }

    /** @test */
    public function testTransitTimeMin()
    {
        $service = new Service();
        $this->assertEquals(5, $service->setTransitTimeMin(5)->getTransitTimeMin());
    }

    /** @test */
    public function testTransitTimeMax()
    {
        $service = new Service();
        $this->assertEquals(576, $service->setTransitTimeMax(576)->getTransitTimeMax());
    }

    /** @test */
    public function testCarrier()
    {
        $service = new Service();

        $mock = $this->getMockClass(CarrierInterface::class);
        $carrier = new $mock();

        $this->assertEquals($carrier, $service->setCarrier($carrier)->getCarrier());
    }

    /** @test */
    public function testRegionFrom()
    {
        $service = new Service();

        $mock = $this->getMockClass(RegionInterface::class);
        $region = new $mock();

        $this->assertEquals($region, $service->setRegionFrom($region)->getRegionFrom());
    }

    /** @test */
    public function testRegionTo()
    {
        $service = new Service();

        $mock = $this->getMockClass(RegionInterface::class);
        $region = new $mock();

        $this->assertEquals($region, $service->setRegionTo($region)->getRegionTo());
    }

    /** @test */
    public function testContracts()
    {
        $service = new Service();

        $mock = $this->getMockClass(ServiceContractInterface::class);
        $contracts = [new $mock(), new $mock(), new $mock()];

        $this->assertCount(3, $contracts);
        $this->assertEquals($contracts, $service->setServiceContracts($contracts)->getServiceContracts());

        $contract = new $mock();
        $contracts[] = $contract;
        $this->assertCount(4, $contracts);
        $this->assertEquals($contracts, $service->addServiceContract($contract)->getServiceContracts());
    }

    /** @test */
    public function testHandoverMethod()
    {
        $service = new Service();

        $this->assertEquals('drop-off', $service->setHandoverMethod('drop-off')->getHandoverMethod());
    }

    /** @test */
    public function testDeliveryDays()
    {
        $service = new Service();

        $this->assertEmpty($service->getDeliveryDays());

        $this->assertEquals(['Thursday'], $service->addDeliveryDay('Thursday')->getDeliveryDays());
        $this->assertEquals(['Tuesday', 'Friday'], $service->setDeliveryDays(['Tuesday', 'Friday'])->getDeliveryDays());
        $this->assertEquals(
            ['Monday', 'Tuesday', 'Friday'],
            $service->addDeliveryDay('Monday')->getDeliveryDays(),
            'Monday should have been added to already existing Tuesday and Friday',
            0.0,
            10,
            true // Order doesn't matter
        );
    }

    /** @test */
    public function testDeliveryMethod()
    {
        $service = new Service();

        $this->assertEquals('pick-up', $service->setDeliveryMethod('pick-up')->getDeliveryMethod());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $carrier = $this->getMockBuilder(CarrierInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $carrier->method('jsonSerialize')
            ->willReturn([
                'id'   => 'carrier-id-1',
                'type' => 'carriers',
            ]);

        $regionFrom = $this->getMockBuilder(RegionInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $regionFrom->method('jsonSerialize')
            ->willReturn([
                'id'   => 'region-id-1',
                'type' => 'regions',
            ]);

        $regionTo = $this->getMockBuilder(RegionInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $regionTo->method('jsonSerialize')
            ->willReturn([
                'id'   => 'region-id-2',
                'type' => 'regions',
            ]);

        $service = (new Service())
            ->setId('service-id')
            ->setName('Easy Delivery Service')
            ->setPackageType(Service::PACKAGE_TYPE_PARCEL)
            ->setTransitTimeMin(7)
            ->setTransitTimeMax(14)
            ->setHandoverMethod('drop-off')
            ->setDeliveryDays(['Monday'])
            ->setCarrier($carrier)
            ->setRegionFrom($regionFrom)
            ->setRegionTo($regionTo);

        $this->assertEquals([
            'id'            => 'service-id',
            'type'          => 'services',
            'attributes'    => [
                'name'            => 'Easy Delivery Service',
                'package_type'    => Service::PACKAGE_TYPE_PARCEL,
                'transit_time'    => [
                    'min' => 7,
                    'max' => 14,
                ],
                'handover_method' => 'drop-off',
                'delivery_days'   => [
                    'Monday',
                ],
            ],
            'relationships' => [
                'carrier'     => [
                    'data' => [
                        'id'   => 'carrier-id-1',
                        'type' => 'carriers',
                    ],
                ],
                'region_from' => [
                    'data' => [
                        'id'   => 'region-id-1',
                        'type' => 'regions',
                    ],
                ],
                'region_to'   => [
                    'data' => [
                        'id'   => 'region-id-2',
                        'type' => 'regions',
                    ],
                ],
            ],
        ], $service->jsonSerialize());
    }
}
