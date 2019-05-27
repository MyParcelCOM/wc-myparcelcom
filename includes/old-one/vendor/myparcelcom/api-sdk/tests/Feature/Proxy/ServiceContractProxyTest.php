<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use GuzzleHttp\ClientInterface;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceGroupInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInsuranceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionPriceInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceContractProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\NullCache;

class ServiceContractProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var ClientInterface */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var ServiceContractProxy */
    private $serviceContractProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api'))
            ->setCache(new NullCache())
            ->setHttpClient($this->client)
            ->authenticate($this->authenticator);

        $this->serviceContractProxy = (new ServiceContractProxy())
            ->setMyParcelComApi($this->api)
            ->setId('f94dda81-c418-4077-ba7c-87ddf9076c28');
    }

    /** @test */
    public function testAccessors()
    {
        /** @var ServiceInterface $service */
        $service = $this->getMockBuilder(ServiceInterface::class)->getMock();
        $this->assertEquals($service, $this->serviceContractProxy->setService($service)->getService());

        /** @var CarrierContractInterface $carrierContract */
        $carrierContract = $this->getMockBuilder(CarrierContractInterface::class)->getMock();
        $this->assertEquals($carrierContract, $this->serviceContractProxy->setCarrierContract($carrierContract)->getCarrierContract());

        $serviceGroupBuilder = $this->getMockBuilder(ServiceGroupInterface::class);
        /** @var ServiceGroupInterface $serviceGroupA */
        $serviceGroupA = $serviceGroupBuilder->getMock();
        /** @var ServiceGroupInterface $serviceGroupB */
        $serviceGroupB = $serviceGroupBuilder->getMock();
        $this->assertEquals(
            [$serviceGroupA, $serviceGroupB],
            $this->serviceContractProxy->setServiceGroups([$serviceGroupA, $serviceGroupB])->getServiceGroups()
        );
        /** @var ServiceGroupInterface $serviceGroupC */
        $serviceGroupC = $serviceGroupBuilder->getMock();
        $this->assertEquals(
            [$serviceGroupA, $serviceGroupB, $serviceGroupC],
            $this->serviceContractProxy->addServiceGroup($serviceGroupC)->getServiceGroups()
        );

        $serviceInsuranceBuilder = $this->getMockBuilder(ServiceInsuranceInterface::class);
        /** @var ServiceInsuranceInterface $serviceInsuranceA */
        $serviceInsuranceA = $serviceInsuranceBuilder->getMock();
        $this->assertEquals(
            [$serviceInsuranceA],
            $this->serviceContractProxy->setServiceInsurances([$serviceInsuranceA])->getServiceInsurances()
        );
        /** @var ServiceInsuranceInterface $serviceInsuranceB */
        $serviceInsuranceB = $serviceInsuranceBuilder->getMock();
        $this->assertEquals(
            [$serviceInsuranceA, $serviceInsuranceB],
            $this->serviceContractProxy->addServiceInsurance($serviceInsuranceB)->getServiceInsurances()
        );

        $serviceOptionPriceBuilder = $this->getMockBuilder(ServiceOptionPriceInterface::class);
        /** @var ServiceOptionPriceInterface $serviceOptionPriceA */
        $serviceOptionPriceA = $serviceOptionPriceBuilder->getMock();
        /** @var ServiceOptionPriceInterface $serviceOptionPriceB */
        $serviceOptionPriceB = $serviceOptionPriceBuilder->getMock();
        /** @var ServiceOptionPriceInterface $serviceOptionPriceC */
        $serviceOptionPriceC = $serviceOptionPriceBuilder->getMock();
        $this->assertEquals(
            [$serviceOptionPriceA, $serviceOptionPriceB, $serviceOptionPriceC],
            $this->serviceContractProxy->setServiceOptionPrices([
                $serviceOptionPriceA,
                $serviceOptionPriceB,
                $serviceOptionPriceC,
            ])->getServiceOptionPrices()
        );
        /** @var ServiceOptionPriceInterface $serviceOptionPriceD */
        $serviceOptionPriceD = $serviceOptionPriceBuilder->getMock();
        $this->assertEquals(
            [$serviceOptionPriceA, $serviceOptionPriceB, $serviceOptionPriceC, $serviceOptionPriceD],
            $this->serviceContractProxy->addServiceOptionPrice($serviceOptionPriceD)->getServiceOptionPrices()
        );

        $this->assertEquals('an-id-for-a-service-contract', $this->serviceContractProxy->setId('an-id-for-a-service-contract')->getId());
    }

    /** @test */
    public function testAttributes()
    {
        $this->assertEquals('f94dda81-c418-4077-ba7c-87ddf9076c28', $this->serviceContractProxy->getId());
        $this->assertEquals(ResourceInterface::TYPE_SERVICE_CONTRACT, $this->serviceContractProxy->getType());
    }

    /** @test */
    public function testServiceRelationship()
    {
        $service = $this->serviceContractProxy->getService();

        $this->assertInstanceOf(ServiceInterface::class, $service);
        $this->assertEquals('a3057e77-005b-4945-a41c-20ddbe4dab08', $service->getId());
        $this->assertEquals('services', $service->getType());
        $this->assertEquals(1, $service->getTransitTimeMin());
        $this->assertEquals(3, $service->getTransitTimeMax());
        $this->assertEquals('Letter Test', $service->getName());
        $this->assertEquals('letter', $service->getPackageType());
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = new ServiceContractProxy();
        $firstProxy
            ->setMyParcelComApi($this->api)
            ->setId('f94dda81-c418-4077-ba7c-87ddf9076c28');
        $firstProxy->getService();
        $firstProxy->getCarrierContract();
        $firstProxy->getServiceGroups();

        $this->assertEquals(1, $this->clientCalls['https://api/v1/service-contracts/f94dda81-c418-4077-ba7c-87ddf9076c28']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new ServiceContractProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('f94dda81-c418-4077-ba7c-87ddf9076c28');
        $secondProxy->getServiceInsurances();

        $this->assertEquals(2, $this->clientCalls['https://api/v1/service-contracts/f94dda81-c418-4077-ba7c-87ddf9076c28']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceProxy = new ServiceContractProxy();
        $serviceProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/v1/service-contracts/f94dda81-c418-4077-ba7c-87ddf9076c28')
            ->setId('service-contract-id-1');

        $this->assertEquals([
            'id'   => 'service-contract-id-1',
            'type' => ResourceInterface::TYPE_SERVICE_CONTRACT,
        ], $serviceProxy->jsonSerialize());
    }
}
