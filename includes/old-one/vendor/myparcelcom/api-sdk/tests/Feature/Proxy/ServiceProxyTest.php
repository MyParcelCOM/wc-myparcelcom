<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use GuzzleHttp\ClientInterface;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\RegionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class ServiceProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var ClientInterface */
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
        $this->api = (new MyParcelComApi('https://api'))
            ->setCache(new NullCache())
            ->setHttpClient($this->client)
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

        $serviceContractBuilder = $this->getMockBuilder(ServiceContractInterface::class);
        /** @var ServiceContractInterface $serviceContractA */
        $serviceContractA = $serviceContractBuilder->getMock();
        $this->assertEquals([$serviceContractA], $this->serviceProxy->setServiceContracts([$serviceContractA])->getServiceContracts());
        /** @var ServiceContractInterface $serviceContractB */
        $serviceContractB = $serviceContractBuilder->getMock();
        $this->assertEquals(
            [$serviceContractA, $serviceContractB],
            $this->serviceProxy->addServiceContract($serviceContractB)->getServiceContracts()
        );
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
    public function testServiceContractRelationship()
    {
        $contract_A = $this->createMock(ServiceContractInterface::class);
        $contract_A
            ->method('getId')
            ->willReturn('contract-id-1');
        $contract_B = $this->createMock(ServiceContractInterface::class);
        $contract_B
            ->method('getId')
            ->willReturn('contract-id-2');

        $contracts = $this->serviceProxy
            ->setServiceContracts([$contract_A, $contract_B])
            ->getServiceContracts();

        array_walk($contracts, function (ServiceContractInterface $contract) {
            $this->assertInstanceOf(ServiceContractInterface::class, $contract);
        });
        $contractIds = array_map(function (ServiceContractInterface $contract) {
            return $contract->getId();
        }, $contracts);
        $this->assertArraySubset(['contract-id-1', 'contract-id-2'], $contractIds);
        $this->assertCount(2, $contracts);

        $contract_C = $this->createMock(ServiceContractInterface::class);
        $contract_C
            ->method('getId')
            ->willReturn('contract-id-3');

        $contracts = $this->serviceProxy
            ->addServiceContract($contract_C)
            ->getServiceContracts();
        $this->assertCount(3, $contracts);
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
        $firstProxy->getServiceContracts();
        $firstProxy->getRegionTo();
        $firstProxy->getDeliveryDays();

        $this->assertEquals(1, $this->clientCalls['https://api/v1/services/433285bb-2e34-435c-9109-1120e7c4bce4']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new ServiceProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('433285bb-2e34-435c-9109-1120e7c4bce4');
        $secondProxy->getTransitTimeMax();

        $this->assertEquals(2, $this->clientCalls['https://api/v1/services/433285bb-2e34-435c-9109-1120e7c4bce4']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceProxy = new ServiceProxy();
        $serviceProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/v1/services/433285bb-2e34-435c-9109-1120e7c4bce4')
            ->setId('service-id-1');

        $this->assertEquals([
            'id'   => 'service-id-1',
            'type' => ResourceInterface::TYPE_SERVICE,
        ], $serviceProxy->jsonSerialize());
    }
}
