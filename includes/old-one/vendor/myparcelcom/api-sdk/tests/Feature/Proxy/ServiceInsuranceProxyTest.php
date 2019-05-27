<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use GuzzleHttp\ClientInterface;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceInsuranceProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class ServiceInsuranceProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var ClientInterface */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var ServiceInsuranceProxy */
    private $serviceInsuranceProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api'))
            ->setCache(new NullCache())
            ->setHttpClient($this->client)
            ->authenticate($this->authenticator);

        $this->serviceInsuranceProxy = (new ServiceInsuranceProxy())
            ->setMyParcelComApi($this->api)
            ->setId('9d5d631d-af3b-4b0a-99e5-5995688a8e88');
    }

    /** @test */
    public function testAccessors()
    {
        $this->assertEquals('JPY', $this->serviceInsuranceProxy->setCurrency('JPY')->getCurrency());
        $this->assertEquals(1100, $this->serviceInsuranceProxy->setPrice(1100)->getPrice());
        $this->assertEquals(6547, $this->serviceInsuranceProxy->setCovered(6547)->getCovered());
        $this->assertEquals('an-id-for-an-insurance', $this->serviceInsuranceProxy->setId('an-id-for-an-insurance')->getId());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('EUR', $this->serviceInsuranceProxy->getCurrency());
        $this->assertEquals(1200, $this->serviceInsuranceProxy->getPrice());
        $this->assertEquals(25000, $this->serviceInsuranceProxy->getCovered());
        $this->assertEquals('9d5d631d-af3b-4b0a-99e5-5995688a8e88', $this->serviceInsuranceProxy->getId());
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = new ServiceInsuranceProxy();
        $firstProxy
            ->setMyParcelComApi($this->api)
            ->setId('9d5d631d-af3b-4b0a-99e5-5995688a8e88');
        $firstProxy->getCurrency();
        $firstProxy->getPrice();

        $this->assertEquals(1, $this->clientCalls['https://api/v1/service-insurances/9d5d631d-af3b-4b0a-99e5-5995688a8e88']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new ServiceInsuranceProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('9d5d631d-af3b-4b0a-99e5-5995688a8e88');
        $secondProxy->getCovered();

        $this->assertEquals(2, $this->clientCalls['https://api/v1/service-insurances/9d5d631d-af3b-4b0a-99e5-5995688a8e88']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceProxy = new ServiceInsuranceProxy();
        $serviceProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/v1/service-insurances/9d5d631d-af3b-4b0a-99e5-5995688a8e88')
            ->setId('service-insurance-id-1');

        $this->assertEquals([
            'id'   => 'service-insurance-id-1',
            'type' => ResourceInterface::TYPE_SERVICE_INSURANCE,
        ], $serviceProxy->jsonSerialize());
    }
}
