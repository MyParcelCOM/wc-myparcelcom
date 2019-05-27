<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceGroupInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInsuranceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionPriceInterface;
use MyParcelCom\ApiSdk\Resources\ServiceContract;
use PHPUnit\Framework\TestCase;

class ServiceContractTest extends TestCase
{

    /** @test */
    public function testId()
    {
        $contract = new ServiceContract();
        $this->assertEquals('contract-id', $contract->setId('contract-id')->getId());
    }

    /** @test */
    public function testGetType()
    {
        $contract = new ServiceContract();
        $this->assertEquals('service-contracts', $contract->getType());
    }

    /** @test */
    public function testService()
    {
        $contract = new ServiceContract();

        $mock = $this->getMockClass(ServiceInterface::class);
        $service = new $mock();

        $this->assertEquals($service, $contract->setService($service)->getService());
    }

    /** @test */
    public function testCarrierContract()
    {
        $contract = new ServiceContract();

        $mock = $this->getMockClass(CarrierContractInterface::class);
        $carrierContract = new $mock();

        $this->assertEquals($carrierContract, $contract->setCarrierContract($carrierContract)->getCarrierContract());
    }

    /** @test */
    public function testServiceGroups()
    {
        $contract = new ServiceContract();

        $this->assertEmpty($contract->getServiceGroups());

        $mock = $this->getMockClass(ServiceGroupInterface::class);

        $groups = [
            new $mock(),
            new $mock(),
        ];
        $contract->setServiceGroups($groups);
        $this->assertCount(2, $contract->getServiceGroups());
        $this->assertEquals($groups, $contract->getServiceGroups());

        $group = new $mock();
        $contract->addServiceGroup($group);
        $groups[] = $group;
        $this->assertCount(3, $contract->getServiceGroups());
        $this->assertEquals($groups, $contract->getServiceGroups());
    }

    /** @test */
    public function testServiceOptionPrices()
    {
        $contract = new ServiceContract();

        $this->assertEmpty($contract->getServiceOptionPrices());

        $mock = $this->getMockClass(ServiceOptionPriceInterface::class);

        $options = [
            new $mock(),
            new $mock(),
        ];
        $contract->setServiceOptionPrices($options);
        $this->assertCount(2, $contract->getServiceOptionPrices());
        $this->assertEquals($options, $contract->getServiceOptionPrices());

        $option = new $mock();
        $contract->addServiceOptionPrice($option);
        $options[] = $option;
        $this->assertCount(3, $contract->getServiceOptionPrices());
        $this->assertEquals($options, $contract->getServiceOptionPrices());
    }

    /** @test */
    public function testServiceInsurances()
    {
        $contract = new ServiceContract();

        $this->assertEmpty($contract->getServiceGroups());

        $mock = $this->getMockClass(ServiceInsuranceInterface::class);

        $insurances = [
            new $mock(),
            new $mock(),
        ];
        $contract->setServiceInsurances($insurances);
        $this->assertCount(2, $contract->getServiceInsurances());
        $this->assertEquals($insurances, $contract->getServiceInsurances());

        $insurance = new $mock();
        $contract->addServiceInsurance($insurance);
        $insurances[] = $insurance;
        $this->assertCount(3, $contract->getServiceInsurances());
        $this->assertEquals($insurances, $contract->getServiceInsurances());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $carrierContractMock = $this->getMockBuilder(CarrierContractInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $carrierContractMock->method('jsonSerialize')
            ->willReturn([
                'type' => 'carrier-contracts',
                'id'   => 'carrier-contract-id',
            ]);

        $serviceMock = $this->getMockBuilder(ServiceInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $serviceMock->method('jsonSerialize')
            ->willReturn([
                'type' => 'services',
                'id'   => 'service-id',
            ]);

        $groupMock = $this->getMockBuilder(ServiceGroupInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $groupMock->method('jsonSerialize')
            ->willReturn([
                'type' => 'service-groups',
                'id'   => 'service-group-id',
            ]);

        $optionMock = $this->getMockBuilder(ServiceOptionPriceInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $optionMock->method('jsonSerialize')
            ->willReturn([
                'type' => 'service-options',
                'id'   => 'service-option-id',
            ]);

        $insuranceMock = $this->getMockBuilder(ServiceInsuranceInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $insuranceMock->method('jsonSerialize')
            ->willReturn([
                'type' => 'service-insurances',
                'id'   => 'service-insurance-id',
            ]);

        $contract = (new ServiceContract())
            ->setId('contract-id')
            ->setService($serviceMock)
            ->setCarrierContract($carrierContractMock)
            ->setServiceInsurances([$insuranceMock])
            ->setServiceGroups([$groupMock])
            ->setServiceOptionPrices([$optionMock]);

        $this->assertEquals([
            'id'            => 'contract-id',
            'type'          => 'service-contracts',
            'relationships' => [
                'service'               => [
                    'data' => [
                        'type' => 'services',
                        'id'   => 'service-id',
                    ],
                ],
                'carrier_contract'      => [
                    'data' => [
                        'type' => 'carrier-contracts',
                        'id'   => 'carrier-contract-id',
                    ],
                ],
                'service_groups'        => [
                    'data' => [
                        [
                            'type' => 'service-groups',
                            'id'   => 'service-group-id',
                        ],
                    ],
                ],
                'service_option_prices' => [
                    'data' => [
                        [
                            'type' => 'service-options',
                            'id'   => 'service-option-id',
                        ],
                    ],
                ],
                'service_insurances'    => [
                    'data' => [
                        [
                            'type' => 'service-insurances',
                            'id'   => 'service-insurance-id',
                        ],
                    ],
                ],
            ],
        ], $contract->jsonSerialize());
    }
}
