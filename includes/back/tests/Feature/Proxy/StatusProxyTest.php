<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use Http\Client\HttpClient;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\StatusProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class StatusProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var HttpClient */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var StatusProxy */
    private $statusProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api', $this->client))
            ->setCache(new NullCache())
            ->authenticate($this->authenticator);

        $this->statusProxy = (new StatusProxy())
            ->setMyParcelComApi($this->api)
            ->setId('status-id-1');
    }

    /** @test */
    public function testAccessors()
    {
        $this->assertEquals('Sketch', $this->statusProxy->setName('Sketch')->getName());
        $this->assertEquals('A sketch of a parcel', $this->statusProxy->setDescription('A sketch of a parcel')->getDescription());
        $this->assertEquals('parcel-sketch', $this->statusProxy->setCode('parcel-sketch')->getCode());
        $this->assertEquals('shipments', $this->statusProxy->setResourceType('shipments')->getResourceType());
        $this->assertEquals('concept', $this->statusProxy->setLevel('concept')->getLevel());
        $this->assertEquals('an-id-for-a-status', $this->statusProxy->setId('an-id-for-a-status')->getId());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('status-id-1', $this->statusProxy->getId());
        $this->assertEquals(ResourceInterface::TYPE_STATUS, $this->statusProxy->getType());
        $this->assertEquals('shipment-delivered', $this->statusProxy->getCode());
        $this->assertEquals('shipments', $this->statusProxy->getResourceType());
        $this->assertEquals('success', $this->statusProxy->getLevel());
        $this->assertEquals('Delivered', $this->statusProxy->getName());
        $this->assertEquals('The shipment has been delivered', $this->statusProxy->getDescription());
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = new StatusProxy();
        $firstProxy
            ->setMyParcelComApi($this->api)
            ->setId('status-id-1');
        $firstProxy->getName();
        $firstProxy->getLevel();
        $firstProxy->getResourceType();
        $firstProxy->getCode();

        $this->assertEquals(1, $this->clientCalls['https://api/statuses/status-id-1']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new StatusProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('status-id-1');
        $secondProxy->getDescription();

        $this->assertEquals(2, $this->clientCalls['https://api/statuses/status-id-1']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $statusProxy = new StatusProxy();
        $statusProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/statuses/status-id-1')
            ->setId('status-id-1');

        $this->assertEquals([
            'id'   => 'status-id-1',
            'type' => ResourceInterface::TYPE_STATUS,
        ], $statusProxy->jsonSerialize());
    }
}
