<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use GuzzleHttp\ClientInterface;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceOptionPriceProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class ServiceOptionPriceProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var ClientInterface */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var ServiceOptionPriceProxy */
    private $serviceOptionPriceProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api'))
            ->setCache(new NullCache())
            ->setHttpClient($this->client)
            ->authenticate($this->authenticator);

        $this->serviceOptionPriceProxy = (new ServiceOptionPriceProxy())
            ->setMyParcelComApi($this->api)
            ->setId('a6e63d46-5395-49a2-9111-df80f15b35de');
    }

    /** @test */
    public function testAccessors()
    {
        $this->assertEquals('JPY', $this->serviceOptionPriceProxy->setCurrency('JPY')->getCurrency());
        $this->assertEquals(1100, $this->serviceOptionPriceProxy->setPrice(1100)->getPrice());
        $this->assertFalse($this->serviceOptionPriceProxy->setRequired(false)->isRequired());
        $this->assertEquals('an-id-for-an-insurance', $this->serviceOptionPriceProxy->setId('an-id-for-an-insurance')->getId());

        /** @var ServiceContractInterface $serviceContract */
        $serviceContract = $this->getMockBuilder(ServiceContractInterface::class)->getMock();
        $this->assertEquals($serviceContract, $this->serviceOptionPriceProxy->setServiceContract($serviceContract)->getServiceContract());

        /** @var ServiceOptionInterface $serviceOption */
        $serviceOption = $this->getMockBuilder(ServiceOptionInterface::class)->getMock();
        $this->assertEquals($serviceOption, $this->serviceOptionPriceProxy->setServiceOption($serviceOption)->getServiceOption());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('EUR', $this->serviceOptionPriceProxy->getCurrency());
        $this->assertEquals(250, $this->serviceOptionPriceProxy->getPrice());
        $this->assertTrue($this->serviceOptionPriceProxy->isRequired());
        $this->assertEquals('a6e63d46-5395-49a2-9111-df80f15b35de', $this->serviceOptionPriceProxy->getId());
    }

    /** @test */
    public function testServiceOptionRelationship()
    {
        $serviceOption = $this->serviceOptionPriceProxy->getServiceOption();

        $this->assertInstanceOf(ServiceOptionInterface::class, $serviceOption);
        $this->assertEquals('d4637e6a-4b7a-44c8-8b4d-8311d0cf1238', $serviceOption->getId());
        $this->assertEquals('service-options', $serviceOption->getType());
        $this->assertEquals('Collection', $serviceOption->getName());
        $this->assertEquals('handover-method', $serviceOption->getCategory());
        $this->assertEquals('handover-method-collection', $serviceOption->getCode());
    }

    /** @test */
    public function testServiceContractRelationship()
    {
        $serviceContract = $this->serviceOptionPriceProxy->getServiceContract();

        $this->assertInstanceOf(ServiceContractInterface::class, $serviceContract);
        $this->assertEquals('48533f26-8502-43f4-a83a-ebbadc238024', $serviceContract->getId());
        $this->assertEquals('service-contracts', $serviceContract->getType());
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = new ServiceOptionPriceProxy();
        $firstProxy
            ->setMyParcelComApi($this->api)
            ->setId('a6e63d46-5395-49a2-9111-df80f15b35de');
        $firstProxy->getCurrency();
        $firstProxy->getPrice();
        $firstProxy->isRequired();

        $this->assertEquals(1, $this->clientCalls['https://api/v1/service-option-prices/a6e63d46-5395-49a2-9111-df80f15b35de']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new ServiceOptionPriceProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('a6e63d46-5395-49a2-9111-df80f15b35de');
        $secondProxy->getServiceContract();

        $this->assertEquals(2, $this->clientCalls['https://api/v1/service-option-prices/a6e63d46-5395-49a2-9111-df80f15b35de']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceProxy = new ServiceOptionPriceProxy();
        $serviceProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/v1/service-option-prices/a6e63d46-5395-49a2-9111-df80f15b35de')
            ->setId('service-option-price-id-1');

        $this->assertEquals([
            'id'   => 'service-option-price-id-1',
            'type' => ResourceInterface::TYPE_SERVICE_OPTION_PRICE,
        ], $serviceProxy->jsonSerialize());
    }
}
