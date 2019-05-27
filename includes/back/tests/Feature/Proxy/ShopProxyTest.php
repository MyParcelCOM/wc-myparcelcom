<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use DateTime;
use Http\Client\HttpClient;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\AddressInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\ShopProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class ShopProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var HttpClient */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var ShopProxy */
    private $shopProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api', $this->client))
            ->setCache(new NullCache())
            ->authenticate($this->authenticator);

        $this->shopProxy = (new ShopProxy())
            ->setMyParcelComApi($this->api)
            ->setId('shop-id-1');
    }

    /** @test */
    public function testAccessors()
    {
        $now = new DateTime();
        $this->assertEquals($now->getTimestamp(), $this->shopProxy->setCreatedAt($now)->getCreatedAt()->getTimestamp());
        $this->assertEquals('https://www.onestop.shop', $this->shopProxy->setWebsite('https://www.onestop.shop')->getWebsite());
        $this->assertEquals('One Stop Shop', $this->shopProxy->setName('One Stop Shop')->getName());
        $this->assertEquals('an-id-for-a-shop', $this->shopProxy->setId('an-id-for-a-shop')->getId());

        $addressBuilder = $this->getMockBuilder(AddressInterface::class);
        /** @var AddressInterface $billingAddress */
        $billingAddress = $addressBuilder->getMock();
        $this->assertEquals($billingAddress, $this->shopProxy->setBillingAddress($billingAddress)->getBillingAddress());

        /** @var AddressInterface $returnAddress */
        $returnAddress = $addressBuilder->getMock();
        $this->assertEquals($returnAddress, $this->shopProxy->setReturnAddress($returnAddress)->getReturnAddress());

        /** @var AddressInterface $senderAddress */
        $senderAddress = $addressBuilder->getMock();
        $this->assertEquals($senderAddress, $this->shopProxy->setSenderAddress($senderAddress)->getSenderAddress());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('shop-id-1', $this->shopProxy->getId());
        $this->assertEquals(ResourceInterface::TYPE_SHOP, $this->shopProxy->getType());
        $this->assertEquals('Testshop', $this->shopProxy->getName());
        $this->assertEquals('https://test.shop', $this->shopProxy->getWebsite());

        $billingAddress = $this->shopProxy->getBillingAddress();
        $this->assertInstanceOf(AddressInterface::class, $billingAddress);
        $this->assertEquals('1AA BB2', $billingAddress->getPostalCode());
        $this->assertEquals('London', $billingAddress->getCity());
        $this->assertEquals('Mister', $billingAddress->getFirstName());

        $returnAddress = $this->shopProxy->getReturnAddress();
        $this->assertInstanceOf(AddressInterface::class, $returnAddress);
        $this->assertEquals('GB', $returnAddress->getCountryCode());
        $this->assertEquals('Return', $returnAddress->getLastName());
        $this->assertEquals('info@myparcel.com', $returnAddress->getEmail());

        $this->assertInstanceOf(DateTime::class, $this->shopProxy->getCreatedAt());
        $this->assertEquals(1509378904, $this->shopProxy->getCreatedAt()->getTimestamp());
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = new ShopProxy();
        $firstProxy
            ->setMyParcelComApi($this->api)
            ->setId('shop-id-1');
        $firstProxy->getCreatedAt();
        $firstProxy->getBillingAddress();
        $firstProxy->getName();

        $this->assertEquals(1, $this->clientCalls['https://api/shops/shop-id-1']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new ShopProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('shop-id-1');
        $secondProxy->getReturnAddress();

        $this->assertEquals(2, $this->clientCalls['https://api/shops/shop-id-1']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $shopProxy = new ShopProxy();
        $shopProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/shops/shop-id-1')
            ->setId('shop-id-1');

        $this->assertEquals([
            'id'   => 'shop-id-1',
            'type' => ResourceInterface::TYPE_SHOP,
        ], $shopProxy->jsonSerialize());
    }
}
