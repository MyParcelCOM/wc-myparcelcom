<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\Contract;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use PHPUnit\Framework\TestCase;

class ContractTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $contract = new Contract();
        $this->assertEquals('contract-id', $contract->setId('contract-id')->getId());
    }

    /** @test */
    public function testGetType()
    {
        $contract = new Contract();
        $this->assertEquals('contracts', $contract->getType());
    }

    /** @test */
    public function testCurrency()
    {
        $contract = new Contract();
        $this->assertEquals('ABC', $contract->setCurrency('ABC')->getCurrency());
    }

    /** @test */
    public function testCarrier()
    {
        $contract = new Contract();

        $mock = $this->getMockClass(CarrierInterface::class);
        $carrier = new $mock();

        $this->assertEquals($carrier, $contract->setCarrier($carrier)->getCarrier());
    }

    /** @test */
    public function testItSetsAndGetsStatus()
    {
        $contract = new Contract();
        $this->assertEquals('inactive', $contract->setStatus('inactive')->getStatus());
    }

    /** @test */
    public function testJsonSerialize()
    {
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

        $contract = (new Contract())
            ->setId('contract-id')
            ->setCurrency('IOU')
            ->setCarrier($carrierMock)
            ->setStatus('invalid');

        $this->assertEquals([
            'id'            => 'contract-id',
            'type'          => 'contracts',
            'attributes'    => [
                'currency' => 'IOU',
                'status'   => 'invalid',
            ],
            'relationships' => [
                'carrier' => [
                    'data' => [
                        'id'   => 'carrier-id',
                        'type' => 'carriers',
                    ],
                ],
            ],
        ], $contract->jsonSerialize());
    }
}
