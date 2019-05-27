<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use Http\Client\HttpClient;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\ContractProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class ContractProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var HttpClient */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var ContractProxy */
    private $contractProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api', $this->client))
            ->setCache(new NullCache())
            ->authenticate($this->authenticator);

        $this->contractProxy = (new ContractProxy())
            ->setMyParcelComApi($this->api)
            ->setId('f1630e62-4645-448d-af22-7d5bac0f502d');
    }

    /** @test */
    public function testAccessors()
    {
        $this->assertEquals('USD', $this->contractProxy->setCurrency('USD')->getCurrency());
        $this->assertEquals('an-id-for-a-contract', $this->contractProxy->setId('an-id-for-a-contract')->getId());

        /** @var CarrierInterface $carrier */
        $carrier = $this->getMockBuilder(CarrierInterface::class)->getMock();
        $this->assertEquals($carrier, $this->contractProxy->setCarrier($carrier)->getCarrier());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('f1630e62-4645-448d-af22-7d5bac0f502d', $this->contractProxy->getId());
        $this->assertEquals(ResourceInterface::TYPE_CONTRACT, $this->contractProxy->getType());
        $this->assertEquals('EUR', $this->contractProxy->getCurrency());
        $this->assertEquals('verified', $this->contractProxy->getStatus());
    }

    /** @test */
    public function testCarrierRelationship()
    {
        $carrier = $this->contractProxy->getCarrier();

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
        $firstProxy = new ContractProxy();
        $firstProxy
            ->setMyParcelComApi($this->api)
            ->setId('f1630e62-4645-448d-af22-7d5bac0f502d');
        $firstProxy->getCurrency();
        $firstProxy->getCarrier();

        $this->assertEquals(1, $this->clientCalls['https://api/contracts/f1630e62-4645-448d-af22-7d5bac0f502d']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new ContractProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('f1630e62-4645-448d-af22-7d5bac0f502d');
        $secondProxy->getCarrier();

        $this->assertEquals(2, $this->clientCalls['https://api/contracts/f1630e62-4645-448d-af22-7d5bac0f502d']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceProxy = new ContractProxy();
        $serviceProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/contracts/f1630e62-4645-448d-af22-7d5bac0f502d')
            ->setId('contract-id-1');

        $this->assertEquals([
            'id'   => 'contract-id-1',
            'type' => ResourceInterface::TYPE_CONTRACT,
        ], $serviceProxy->jsonSerialize());
    }
}
