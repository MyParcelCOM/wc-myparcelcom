<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\CarrierContract;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use PHPUnit\Framework\TestCase;

class CarrierContractTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $contract = new CarrierContract();
        $this->assertEquals('contract-id', $contract->setId('contract-id')->getId());
    }

    /** @test */
    public function testGetType()
    {
        $contract = new CarrierContract();
        $this->assertEquals('carrier-contracts', $contract->getType());
    }

    /** @test */
    public function testCurrency()
    {
        $contract = new CarrierContract();
        $this->assertEquals('ABC', $contract->setCurrency('ABC')->getCurrency());
    }

    /** @test */
    public function testCarrier()
    {
        $contract = new CarrierContract();

        $mock = $this->getMockClass(CarrierInterface::class);
        $carrier = new $mock();

        $this->assertEquals($carrier, $contract->setCarrier($carrier)->getCarrier());
    }

    /** @test */
    public function testServiceContracts()
    {
        $contract = new CarrierContract();

        $mock = $this->getMockClass(ServiceContractInterface::class);
        $serviceContracts = [new $mock(), new $mock(), new $mock()];

        $this->assertEquals($serviceContracts, $contract->setServiceContracts($serviceContracts)->getServiceContracts());

        $serviceContract = new $mock();
        $serviceContracts[] = $serviceContract;
        $this->assertEquals($serviceContracts, $contract->addServiceContract($serviceContract)->getServiceContracts());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceContractMockBuilder = $this->getMockBuilder(ServiceContractInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes();

        $serviceContractMockA = $serviceContractMockBuilder->getMock();
        $serviceContractMockA->method('jsonSerialize')
            ->willReturn([
                'type' => 'service-contracts',
                'id'   => 'service-contract-id-1',
            ]);

        $serviceContractMockB = $serviceContractMockBuilder->getMock();
        $serviceContractMockB->method('jsonSerialize')
            ->willReturn([
                'type' => 'service-contracts',
                'id'   => 'service-contract-id-2',
            ]);

        $serviceContractMockC = $serviceContractMockBuilder->getMock();
        $serviceContractMockC->method('jsonSerialize')
            ->willReturn([
                'type' => 'service-contracts',
                'id'   => 'service-contract-id-3',
            ]);

        $carrierMock = $this->getMockBuilder(CarrierInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $carrierMock->method('jsonSerialize')
            ->willReturn([
                'type' => 'carriers',
                'id'   => 'carrier-id',
            ]);

        $contract = (new CarrierContract())
            ->setId('contract-id')
            ->setCurrency('IOU')
            ->setCarrier($carrierMock)
            ->setServiceContracts([
                $serviceContractMockA,
                $serviceContractMockB,
                $serviceContractMockC,
            ]);

        $this->assertEquals([
            'id'            => 'contract-id',
            'type'          => 'carrier-contracts',
            'attributes'    => [
                'currency' => 'IOU',
            ],
            'relationships' => [
                'carrier'           => [
                    'data' => [
                        'id'   => 'carrier-id',
                        'type' => 'carriers',
                    ],
                ],
                'service_contracts' => [
                    'data' => [
                        [
                            'type' => 'service-contracts',
                            'id'   => 'service-contract-id-1',
                        ],
                        [
                            'type' => 'service-contracts',
                            'id'   => 'service-contract-id-2',
                        ],
                        [
                            'type' => 'service-contracts',
                            'id'   => 'service-contract-id-3',
                        ],
                    ],
                ],
            ],
        ], $contract->jsonSerialize());
    }
}
