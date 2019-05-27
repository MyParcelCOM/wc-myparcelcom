<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use GuzzleHttp\ClientInterface;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\StatusInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\ShipmentProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\ShipmentStatusProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class ShipmentStatusProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var ClientInterface */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var ShipmentStatusProxy */
    private $shipmentStatusProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api'))
            ->setCache(new NullCache())
            ->setHttpClient($this->client)
            ->authenticate($this->authenticator);

        $this->shipmentStatusProxy = (new ShipmentProxy())
            ->setMyParcelComApi($this->api)
            ->setId('shipment-id-1')
            ->getShipmentStatus();
    }

    /** @test */
    public function testAccessors()
    {
        $now = new \DateTime();
        $this->assertEquals($now->getTimestamp(), $this->shipmentStatusProxy->setCarrierTimestamp($now)->getCarrierTimestamp()->getTimestamp());
        $this->assertEquals('A12', $this->shipmentStatusProxy->setCarrierStatusCode('A12')->getCarrierStatusCode());
        $this->assertEquals('Something go wrongo', $this->shipmentStatusProxy->setCarrierStatusDescription('Something go wrongo')->getCarrierStatusDescription());
        $this->assertEquals('an-id-for-a-shipment-status', $this->shipmentStatusProxy->setId('an-id-for-a-shipment-status')->getId());

        /** @var ShipmentInterface $shipment */
        $shipment = $this->getMockBuilder(ShipmentInterface::class)->getMock();
        $this->assertEquals($shipment, $this->shipmentStatusProxy->setShipment($shipment)->getShipment());

        /** @var StatusInterface $status */
        $status = $this->getMockBuilder(StatusInterface::class)->getMock();
        $this->assertEquals($status, $this->shipmentStatusProxy->setStatus($status)->getStatus());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('shipment-status-id-1', $this->shipmentStatusProxy->getId());
        $this->assertEquals(ResourceInterface::TYPE_SHIPMENT_STATUS, $this->shipmentStatusProxy->getType());
        $this->assertEquals('9001', $this->shipmentStatusProxy->getCarrierStatusCode());
        $this->assertEquals('Confirmed at destination', $this->shipmentStatusProxy->getCarrierStatusDescription());
        $this->assertEquals(1504801719, $this->shipmentStatusProxy->getCarrierTimestamp()->getTimestamp());
    }

    /** @test */
    public function testShipmentRelationship()
    {
        $shipment = $this->shipmentStatusProxy->getShipment();
        $this->assertInstanceOf(ShipmentInterface::class, $shipment);
        $this->assertEquals('shipment-id-1', $shipment->getId());
        $this->assertEquals(ResourceInterface::TYPE_SHIPMENT, $shipment->getType());
    }

    /** @test */
    public function testStatusRelationship()
    {
        $status = $this->shipmentStatusProxy->getStatus();
        $this->assertInstanceOf(StatusInterface::class, $status);
        $this->assertEquals('status-id-1', $status->getId());
        $this->assertEquals(ResourceInterface::TYPE_STATUS, $status->getType());
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = (new ShipmentProxy())
            ->setMyParcelComApi($this->api)
            ->setId('shipment-id-1')
            ->getShipmentStatus();

        $firstProxy->getCarrierTimestamp();
        $firstProxy->getCarrierStatusDescription();
        $firstProxy->getCarrierStatusCode();

        $this->assertEquals(
            1,
            $this->clientCalls['https://api/v1/shipments/shipment-id-1/statuses/shipment-status-id-1']
        );

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = (new ShipmentProxy())
            ->setMyParcelComApi($this->api)
            ->setId('shipment-id-1')
            ->getShipmentStatus();

        $secondProxy->getStatus();

        $this->assertEquals(
            2,
            $this->clientCalls['https://api/v1/shipments/shipment-id-1/statuses/shipment-status-id-1']
        );
    }

    /** @test */
    public function testJsonSerialize()
    {
        $shipmentStatusProxy = (new ShipmentProxy())
            ->setMyParcelComApi($this->api)
            ->setId('shipment-id-1')
            ->getShipmentStatus();

        $this->assertEquals([
            'id'   => 'shipment-status-id-1',
            'type' => ResourceInterface::TYPE_SHIPMENT_STATUS,
        ], $shipmentStatusProxy->jsonSerialize());
    }
}
