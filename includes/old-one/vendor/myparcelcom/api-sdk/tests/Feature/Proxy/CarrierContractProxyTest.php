<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use GuzzleHttp\ClientInterface;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\CarrierContractProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class CarrierContractProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var ClientInterface */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var CarrierContractProxy */
    private $carrierContractProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api'))
            ->setCache(new NullCache())
            ->setHttpClient($this->client)
            ->authenticate($this->authenticator);

        $this->carrierContractProxy = (new CarrierContractProxy())
            ->setMyParcelComApi($this->api)
            ->setId('f1630e62-4645-448d-af22-7d5bac0f502d');
    }

    /** @test */
    public function testAccessors()
    {
        $this->assertEquals('USD', $this->carrierContractProxy->setCurrency('USD')->getCurrency());
        $this->assertEquals('an-id-for-a-contract', $this->carrierContractProxy->setId('an-id-for-a-contract')->getId());

        /** @var CarrierInterface $carrier */
        $carrier = $this->getMockBuilder(CarrierInterface::class)->getMock();
        $this->assertEquals($carrier, $this->carrierContractProxy->setCarrier($carrier)->getCarrier());

        $serviceContractBuilder = $this->getMockBuilder(ServiceContractInterface::class);
        /** @var ServiceContractInterface $serviceContractA */
        $serviceContractA = $serviceContractBuilder->getMock();
        $this->assertEquals([$serviceContractA], $this->carrierContractProxy->setServiceContracts([$serviceContractA])->getServiceContracts());

        /** @var ServiceContractInterface $serviceContractB */
        $serviceContractB = $serviceContractBuilder->getMock();
        $this->assertEquals([
            $serviceContractA,
            $serviceContractB,
        ], $this->carrierContractProxy->addServiceContract($serviceContractB)->getServiceContracts());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('f1630e62-4645-448d-af22-7d5bac0f502d', $this->carrierContractProxy->getId());
        $this->assertEquals(ResourceInterface::TYPE_CARRIER_CONTRACT, $this->carrierContractProxy->getType());
        $this->assertEquals('EUR', $this->carrierContractProxy->getCurrency());
    }

    /** @test */
    public function testCarrierRelationship()
    {
        $carrier = $this->carrierContractProxy->getCarrier();

        $this->assertInstanceOf(CarrierInterface::class, $carrier);
        $this->assertEquals('carriers', $carrier->getType());
        $this->assertEquals('eef00b32-177e-43d3-9b26-715365e4ce46', $carrier->getId());
        $this->assertEquals('Test Carrier', $carrier->getName());
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = new CarrierContractProxy();
        $firstProxy
            ->setMyParcelComApi($this->api)
            ->setId('f1630e62-4645-448d-af22-7d5bac0f502d');
        $firstProxy->getCurrency();
        $firstProxy->getServiceContracts();

        $this->assertEquals(1, $this->clientCalls['https://api/v1/carrier-contracts/f1630e62-4645-448d-af22-7d5bac0f502d']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new CarrierContractProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('f1630e62-4645-448d-af22-7d5bac0f502d');
        $secondProxy->getCarrier();

        $this->assertEquals(2, $this->clientCalls['https://api/v1/carrier-contracts/f1630e62-4645-448d-af22-7d5bac0f502d']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceProxy = new CarrierContractProxy();
        $serviceProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/v1/carrier-contracts/f1630e62-4645-448d-af22-7d5bac0f502d')
            ->setId('carrier-contract-id-1');

        $this->assertEquals([
            'id'   => 'carrier-contract-id-1',
            'type' => ResourceInterface::TYPE_CARRIER_CONTRACT,
        ], $serviceProxy->jsonSerialize());
    }
}
