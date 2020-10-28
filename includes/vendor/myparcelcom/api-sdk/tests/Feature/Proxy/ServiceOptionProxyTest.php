<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use Http\Client\HttpClient;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceOptionProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class ServiceOptionProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var HttpClient */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var ServiceOptionProxy */
    private $serviceOptionProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api', $this->client))
            ->setCache(new NullCache())
            ->authenticate($this->authenticator);

        $this->serviceOptionProxy = (new ServiceOptionProxy())
            ->setMyParcelComApi($this->api)
            ->setId('d4637e6a-4b7a-44c8-8b4d-8311d0cf1238');
    }

    /** @test */
    public function testAccessors()
    {
        $this->assertEquals('Drone delivery', $this->serviceOptionProxy->setName('Drone delivery')->getName());
        $this->assertEquals('delivery-method', $this->serviceOptionProxy->setCategory('delivery-method')->getCategory());
        $this->assertEquals('delivery-method-drone', $this->serviceOptionProxy->setCode('delivery-method-drone')->getCode());
        $this->assertEquals('an-id-for-a-service-option', $this->serviceOptionProxy->setId('an-id-for-a-service-option')->getId());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('service-options', $this->serviceOptionProxy->getType());
        $this->assertEquals('Collection', $this->serviceOptionProxy->getName());
        $this->assertEquals('handover-method', $this->serviceOptionProxy->getCategory());
        $this->assertEquals('handover-method-collection', $this->serviceOptionProxy->getCode());
        $this->assertEquals('d4637e6a-4b7a-44c8-8b4d-8311d0cf1238', $this->serviceOptionProxy->getId());
    }

    /** @test */
    public function testItSetsAndGetsMetaProperties()
    {
        $this->assertNull($this->serviceOptionProxy->getPrice());
        $this->assertEquals(200, $this->serviceOptionProxy->setPrice(200)->getPrice());

        $this->assertNull($this->serviceOptionProxy->getCurrency());
        $this->assertEquals('GBP', $this->serviceOptionProxy->setCurrency('GBP')->getCurrency());

        $this->assertNull($this->serviceOptionProxy->isIncluded());
        $this->assertTrue($this->serviceOptionProxy->setIncluded(true)->isIncluded());
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = new ServiceOptionProxy();
        $firstProxy
            ->setMyParcelComApi($this->api)
            ->setId('d4637e6a-4b7a-44c8-8b4d-8311d0cf1238');
        $firstProxy->getName();
        $firstProxy->getCategory();

        $this->assertEquals(1, $this->clientCalls['https://api/service-options/d4637e6a-4b7a-44c8-8b4d-8311d0cf1238']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new ServiceOptionProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('d4637e6a-4b7a-44c8-8b4d-8311d0cf1238');
        $secondProxy->getCode();

        $this->assertEquals(2, $this->clientCalls['https://api/service-options/d4637e6a-4b7a-44c8-8b4d-8311d0cf1238']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceProxy = new ServiceOptionProxy();
        $serviceProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/service-options/d4637e6a-4b7a-44c8-8b4d-8311d0cf1238')
            ->setId('service-option-id-1')
            ->setIncluded(false)
            ->setPrice(500)
            ->setCurrency('GBP');

        $this->assertEquals([
            'id'   => 'service-option-id-1',
            'type' => ResourceInterface::TYPE_SERVICE_OPTION,
            'meta' => [
                'included' => false,
                'price'    => [
                    'amount'   => 500,
                    'currency' => 'GBP',
                ],
            ],
        ], $serviceProxy->jsonSerialize());
    }
}
