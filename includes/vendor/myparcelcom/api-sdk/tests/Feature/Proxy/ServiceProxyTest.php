<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use Http\Client\HttpClient;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\RegionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceRateInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class ServiceProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var HttpClient */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var ServiceProxy */
    private $serviceProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api', $this->client))
            ->setCache(new NullCache())
            ->authenticate($this->authenticator);

        $this->serviceProxy = (new ServiceProxy())
            ->setMyParcelComApi($this->api)
            ->setId('433285bb-2e34-435c-9109-1120e7c4bce4');
    }

    /** @test */
    public function testAccessors()
    {
        $this->assertEquals('Super Service Plus', $this->serviceProxy->setName('Super Service Plus')->getName());
        $this->assertEquals(ServiceInterface::PACKAGE_TYPE_LETTER, $this->serviceProxy->setPackageType(ServiceInterface::PACKAGE_TYPE_LETTER)->getPackageType());
        $this->assertEquals(4, $this->serviceProxy->setTransitTimeMin(4)->getTransitTimeMin());
        $this->assertEquals(12, $this->serviceProxy->setTransitTimeMax(12)->getTransitTimeMax());
        $this->assertEquals('drop-off', $this->serviceProxy->setHandoverMethod('drop-off')->getHandoverMethod());
        $this->assertEquals('an-id-for-a-service', $this->serviceProxy->setId('an-id-for-a-service')->getId());

        $this->assertEquals(
            ['Wednesday', 'Friday'],
            $this->serviceProxy->setDeliveryDays(['Wednesday', 'Friday'])->getDeliveryDays()
        );
        $this->serviceProxy->addDeliveryDay('Tuesday');
        $this->assertEquals(
            ['Wednesday', 'Friday', 'Tuesday'],
            $this->serviceProxy->getDeliveryDays()
        );

        /** @var CarrierInterface $carrier */
        $carrier = $this->getMockBuilder(CarrierInterface::class)->getMock();
        $this->assertEquals($carrier, $this->serviceProxy->setCarrier($carrier)->getCarrier());

        $regionBuilder = $this->getMockBuilder(RegionInterface::class);
        /** @var RegionInterface $regionTo */
        $regionTo = $regionBuilder->getMock();
        $this->assertEquals($regionTo, $this->serviceProxy->setRegionTo($regionTo)->getRegionTo());

        /** @var RegionInterface $regionFrom */
        $regionFrom = $regionBuilder->getMock();
        $this->assertEquals($regionFrom, $this->serviceProxy->setRegionFrom($regionFrom)->getRegionFrom());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('433285bb-2e34-435c-9109-1120e7c4bce4', $this->serviceProxy->getId());
        $this->assertEquals(ResourceInterface::TYPE_SERVICE, $this->serviceProxy->getType());
        $this->assertEquals('Letterbox Test', $this->serviceProxy->getName());
        $this->assertEquals('letterbox', $this->serviceProxy->getPackageType());
        $this->assertEquals(2, $this->serviceProxy->getTransitTimeMin());
        $this->assertEquals(3, $this->serviceProxy->getTransitTimeMax());
        $this->assertEquals('collection', $this->serviceProxy->getHandoverMethod());

        $this->assertInternalType('array', $this->serviceProxy->getDeliveryDays());
        $this->assertCount(4, $this->serviceProxy->getDeliveryDays());
        $this->assertEquals([
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
        ], $this->serviceProxy->getDeliveryDays());

        $this->assertEquals('delivery', $this->serviceProxy->getDeliveryMethod());
    }

    /** @test */
    public function testCarrierRelationship()
    {
        $carrier = $this->serviceProxy->getCarrier();
        $this->assertInstanceOf(CarrierInterface::class, $carrier);
        $this->assertEquals('eef00b32-177e-43d3-9b26-715365e4ce46', $carrier->getId());
        $this->assertEquals(ResourceInterface::TYPE_CARRIER, $carrier->getType());
    }

    /** @test */
    public function testRegionFromRelationship()
    {
        $regionFrom = $this->serviceProxy->getRegionFrom();
        $this->assertInstanceOf(RegionInterface::class, $regionFrom);
        $this->assertEquals('c1048135-db45-404e-adac-fdecd0c7134a', $regionFrom->getId());
        $this->assertEquals(ResourceInterface::TYPE_REGION, $regionFrom->getType());
    }

    /** @test */
    public function testRegionToRelationship()
    {
        $regionTo = $this->serviceProxy->getRegionTo();
        $this->assertInstanceOf(RegionInterface::class, $regionTo);
        $this->assertEquals('c1048135-db45-404e-adac-fdecd0c7134a', $regionTo->getId());
        $this->assertEquals(ResourceInterface::TYPE_REGION, $regionTo->getType());
    }

    /** @test */
    public function testServiceRateRelationship()
    {
        $serviceRate_A = $this->createMock(ServiceRateInterface::class);
        $serviceRate_A
            ->method('getId')
            ->willReturn('service-rate-id-1');
        $serviceRate_B = $this->createMock(ServiceRateInterface::class);
        $serviceRate_B
            ->method('getId')
            ->willReturn('service-rate-id-2');

        $serviceRates = $this->serviceProxy
            ->setServiceRates([$serviceRate_A, $serviceRate_B])
            ->getServiceRates();

        array_walk($serviceRates, function (ServiceRateInterface $serviceRate) {
            $this->assertInstanceOf(ServiceRateInterface::class, $serviceRate);
        });
        $serviceRateIds = array_map(function (ServiceRateInterface $serviceRate) {
            return $serviceRate->getId();
        }, $serviceRates);
        $this->assertArraySubset(['service-rate-id-1', 'service-rate-id-2'], $serviceRateIds);
        $this->assertCount(2, $serviceRates);

        $serviceRate_C = $this->createMock(ServiceRateInterface::class);
        $serviceRate_C
            ->method('getId')
            ->willReturn('service-rate-id-3');

        $serviceRates = $this->serviceProxy
            ->addServiceRate($serviceRate_C)
            ->getServiceRates();
        $this->assertCount(3, $serviceRates);
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = new ServiceProxy();
        $firstProxy
            ->setMyParcelComApi($this->api)
            ->setId('433285bb-2e34-435c-9109-1120e7c4bce4');
        $firstProxy->getRegionTo();
        $firstProxy->getDeliveryDays();

        $this->assertEquals(1, $this->clientCalls['https://api/services/433285bb-2e34-435c-9109-1120e7c4bce4']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new ServiceProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('433285bb-2e34-435c-9109-1120e7c4bce4');
        $secondProxy->getTransitTimeMax();

        $this->assertEquals(2, $this->clientCalls['https://api/services/433285bb-2e34-435c-9109-1120e7c4bce4']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceProxy = new ServiceProxy();
        $serviceProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/services/433285bb-2e34-435c-9109-1120e7c4bce4')
            ->setId('service-id-1');

        $this->assertEquals([
            'id'   => 'service-id-1',
            'type' => ResourceInterface::TYPE_SERVICE,
        ], $serviceProxy->jsonSerialize());
    }
}
