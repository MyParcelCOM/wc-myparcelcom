<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\StatusInterface;
use MyParcelCom\ApiSdk\Resources\ShipmentStatus;
use PHPUnit\Framework\TestCase;

class ShipmentStatusTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $shipmentStatus = new ShipmentStatus();
        $this->assertEquals('shipment-status-id', $shipmentStatus->setId('shipment-status-id')->getId());
    }

    /** @test */
    public function testType()
    {
        $shipmentStatus = new ShipmentStatus();
        $this->assertEquals('shipment-statuses', $shipmentStatus->getType());
    }

    /** @test */
    public function testCarrierStatusCode()
    {
        $shipmentStatus = new ShipmentStatus();
        $this->assertEquals('A01', $shipmentStatus->setCarrierStatusCode('A01')->getCarrierStatusCode());
    }

    /** @test */
    public function testCarrierStatusDescription()
    {
        $shipmentStatus = new ShipmentStatus();
        $this->assertEquals('We have received the shipment', $shipmentStatus->setCarrierStatusDescription('We have received the shipment')->getCarrierStatusDescription());
    }

    /** @test */
    public function testCarrierTimestamp()
    {
        $shipmentStatus = new ShipmentStatus();
        $this->assertEquals((new \DateTime())->setTimestamp(1504801719), $shipmentStatus->setCarrierTimestamp(1504801719)->getCarrierTimestamp());
        $this->assertEquals((new \DateTime())->setTimestamp(1504801720), $shipmentStatus->setCarrierTimestamp((new \DateTime())->setTimestamp(1504801720))->getCarrierTimestamp());
    }

    /** @test */
    public function testShipment()
    {
        $mock = $this->getMockClass(ShipmentInterface::class);
        $shipment = new $mock();

        $shipmentStatus = new ShipmentStatus();
        $this->assertEquals($shipment, $shipmentStatus->setShipment($shipment)->getShipment());
    }

    /** @test */
    public function testStatus()
    {
        $mock = $this->getMockClass(StatusInterface::class);
        $status = new $mock();

        $shipmentStatus = new ShipmentStatus();
        $this->assertEquals($status, $shipmentStatus->setStatus($status)->getStatus());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $shipment = $this->getMockBuilder(ShipmentInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $shipment->method('jsonSerialize')
            ->willReturn([
                'id'   => 'shipment-id-1',
                'type' => 'shipments',
            ]);

        $status = $this->getMockBuilder(StatusInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $status->method('jsonSerialize')
            ->willReturn([
                'id'   => 'status-id-1',
                'type' => 'statuses',
            ]);

        $shipmentStatus = (new ShipmentStatus())
            ->setId('shipment-status-id')
            ->setCarrierStatusCode('A01')
            ->setCarrierStatusDescription('We have received the shipment')
            ->setCarrierTimestamp(1504801719)
            ->setShipment($shipment)
            ->setStatus($status);

        $this->assertEquals([
            'id'            => 'shipment-status-id',
            'type'          => 'shipment-statuses',
            'attributes'    => [
                'carrier_status_code'        => 'A01',
                'carrier_status_description' => 'We have received the shipment',
                'carrier_timestamp'          => 1504801719,
            ],
            'relationships' => [
                'status'   => [
                    'data' => [
                        'id'   => 'status-id-1',
                        'type' => 'statuses',
                    ],
                ],
                'shipment' => [
                    'data' => [
                        'id'   => 'shipment-id-1',
                        'type' => 'shipments',
                    ],
                ],
            ],
        ], $shipmentStatus->jsonSerialize());
    }
}
