<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use GuzzleHttp\ClientInterface;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceGroupProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class ServiceGroupProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var ClientInterface */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var ServiceGroupProxy */
    private $serviceGroupProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api'))
            ->setCache(new NullCache())
            ->setHttpClient($this->client)
            ->authenticate($this->authenticator);

        $this->serviceGroupProxy = (new ServiceGroupProxy())
            ->setMyParcelComApi($this->api)
            ->setId('01a4a016-ff29-45ca-832d-92382f9ae243');
    }

    /** @test */
    public function testAccessors()
    {
        $this->assertEquals('NOK', $this->serviceGroupProxy->setCurrency('NOK')->getCurrency());
        $this->assertEquals(12, $this->serviceGroupProxy->setWeightMin(12)->getWeightMin());
        $this->assertEquals(18796, $this->serviceGroupProxy->setWeightMax(18796)->getWeightMax());
        $this->assertEquals(149, $this->serviceGroupProxy->setPrice(149)->getPrice());
        $this->assertEquals(12, $this->serviceGroupProxy->setStepPrice(12)->getStepPrice());
        $this->assertEquals(100, $this->serviceGroupProxy->setStepSize(100)->getStepSize());
        $this->assertEquals('an-id-for-a-group', $this->serviceGroupProxy->setId('an-id-for-a-group')->getId());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('EUR', $this->serviceGroupProxy->getCurrency());
        $this->assertEquals(0, $this->serviceGroupProxy->getWeightMin());
        $this->assertEquals(10000, $this->serviceGroupProxy->getWeightMax());
        $this->assertEquals(800, $this->serviceGroupProxy->getPrice());
        $this->assertEquals(0, $this->serviceGroupProxy->getStepPrice());
        $this->assertEquals(1, $this->serviceGroupProxy->getStepSize());
        $this->assertEquals('01a4a016-ff29-45ca-832d-92382f9ae243', $this->serviceGroupProxy->getId());
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = new ServiceGroupProxy();
        $firstProxy
            ->setMyParcelComApi($this->api)
            ->setId('01a4a016-ff29-45ca-832d-92382f9ae243');
        $firstProxy->getCurrency();
        $firstProxy->getStepSize();
        $firstProxy->getPrice();

        $this->assertEquals(1, $this->clientCalls['https://api/v1/service-groups/01a4a016-ff29-45ca-832d-92382f9ae243']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new ServiceGroupProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('01a4a016-ff29-45ca-832d-92382f9ae243');
        $secondProxy->getStepPrice();

        $this->assertEquals(2, $this->clientCalls['https://api/v1/service-groups/01a4a016-ff29-45ca-832d-92382f9ae243']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceProxy = new ServiceGroupProxy();
        $serviceProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/v1/service-groups/01a4a016-ff29-45ca-832d-92382f9ae243')
            ->setId('service-group-id-1');

        $this->assertEquals([
            'id'   => 'service-group-id-1',
            'type' => ResourceInterface::TYPE_SERVICE_GROUP,
        ], $serviceProxy->jsonSerialize());
    }
}
