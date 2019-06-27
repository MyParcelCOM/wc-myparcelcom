<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use Http\Client\HttpClient;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\RegionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\RegionProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class RegionProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var HttpClient */
    private $client;

    /** @var AuthenticatorInterface */
    private $authenticator;

    /** @var MyParcelComApiInterface */
    private $api;

    /** @var RegionProxy */
    private $regionProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api', $this->client))
            ->setCache(new NullCache())
            ->authenticate($this->authenticator);

        $this->regionProxy = (new RegionProxy())
            ->setMyParcelComApi($this->api)
            ->setId('c1048135-db45-404e-adac-fdecd0c7134a');
    }

    /** @test */
    public function testAccessors()
    {
        $this->assertEquals('NH', $this->regionProxy->setRegionCode('NH')->getRegionCode());
        $this->assertEquals('NL', $this->regionProxy->setCountryCode('NL')->getCountryCode());
        $this->assertEquals('Noord-Holland', $this->regionProxy->setName('Noord-Holland')->getName());
        $this->assertEquals('EUR', $this->regionProxy->setCurrency('EUR')->getCurrency());
        $this->assertEquals('an-id-for-a-region', $this->regionProxy->setId('an-id-for-a-region')->getId());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('c1048135-db45-404e-adac-fdecd0c7134a', $this->regionProxy->getId());
        $this->assertEquals(ResourceInterface::TYPE_REGION, $this->regionProxy->getType());
        $this->assertEquals('GB', $this->regionProxy->getCountryCode());
        $this->assertEquals('ENG', $this->regionProxy->getRegionCode());
        $this->assertEquals('GBP', $this->regionProxy->setCurrency('GBP')->getCurrency());
        $this->assertEquals('United Kingdom', $this->regionProxy->getName());
        $this->assertEquals('country', $this->regionProxy->getCategory());
    }

    /** @test */
    public function testRelationships()
    {
        $parent = $this->regionProxy->getParent();
        $this->assertInstanceOf(RegionInterface::class, $this->regionProxy->getParent());
        $this->assertEquals('regions', $parent->getType());
        $this->assertEquals('3844a94a-49bf-434f-9f67-3ff7ec071821', $parent->getId());
        $this->assertEquals('Europe', $parent->getName());
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = new RegionProxy();
        $firstProxy
            ->setMyParcelComApi($this->api)
            ->setId('c1048135-db45-404e-adac-fdecd0c7134a');
        $firstProxy->getName();
        $firstProxy->getCurrency();
        $firstProxy->getCountryCode();

        $this->assertEquals(1, $this->clientCalls['https://api/regions/c1048135-db45-404e-adac-fdecd0c7134a']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new RegionProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('c1048135-db45-404e-adac-fdecd0c7134a');
        $secondProxy->getName();

        $this->assertEquals(2, $this->clientCalls['https://api/regions/c1048135-db45-404e-adac-fdecd0c7134a']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $regionProxy = (new RegionProxy())
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/regions/c1048135-db45-404e-adac-fdecd0c7134a')
            ->setId('region-proxy-id-1');

        $this->assertEquals([
            'id'   => 'region-proxy-id-1',
            'type' => ResourceInterface::TYPE_REGION,
        ], $regionProxy->jsonSerialize());
    }
}
