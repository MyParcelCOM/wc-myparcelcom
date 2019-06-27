<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use Http\Client\HttpClient;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\CarrierProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class CarrierProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var HttpClient */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var CarrierProxy */
    private $carrierProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api', $this->client))
            ->setCache(new NullCache())
            ->authenticate($this->authenticator);

        $this->carrierProxy = (new CarrierProxy())
            ->setMyParcelComApi($this->api)
            ->setId('eef00b32-177e-43d3-9b26-715365e4ce46');
    }

    /** @test */
    public function testAccessors()
    {
        $this->assertEquals('Carrier Name', $this->carrierProxy->setName('Carrier Name')->getName());
        $this->assertEquals('an-id-for-a-carrier', $this->carrierProxy->setId('an-id-for-a-carrier')->getId());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('eef00b32-177e-43d3-9b26-715365e4ce46', $this->carrierProxy->getId());
        $this->assertEquals(ResourceInterface::TYPE_CARRIER, $this->carrierProxy->getType());
        $this->assertEquals('Test Carrier', $this->carrierProxy->getName());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceProxy = new CarrierProxy();
        $serviceProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/carriers/eef00b32-177e-43d3-9b26-715365e4ce46')
            ->setId('carrier-id-1');

        $this->assertEquals([
            'id'   => 'carrier-id-1',
            'type' => ResourceInterface::TYPE_CARRIER,
        ], $serviceProxy->jsonSerialize());
    }
}
