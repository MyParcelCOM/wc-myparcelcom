<?php

namespace MyParcelCom\ApiSdk\Tests\Feature;

use Http\Client\HttpClient;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\Collection\CollectionInterface;
use MyParcelCom\ApiSdk\Exceptions\InvalidResourceException;
use MyParcelCom\ApiSdk\Http\Exceptions\RequestException;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\Resources\Address;
use MyParcelCom\ApiSdk\Resources\Carrier;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\PickUpDropOffLocationInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\RegionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceRateInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentStatusInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\StatusInterface;
use MyParcelCom\ApiSdk\Resources\Shipment;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;

class MyParcelComApiTest extends TestCase
{
    use MocksApiCommunication;

    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApi */
    private $api;
    /** @var HttpClient */
    private $client;

    public function setUp()
    {
        $this->authenticator = $this->getAuthenticatorMock();

        $this->client = $this->getClientMock();

        $this->api = (new MyParcelComApi('https://api', $this->client))
            ->authenticate($this->authenticator);
    }

    /** @test */
    public function testSingleton()
    {
        $this->assertNull(MyParcelComApi::getSingleton());

        $api = MyParcelComApi::createSingleton($this->authenticator, 'https://api', $this->client);
        $this->assertInstanceOf(MyParcelComApi::class, $api);
        $this->assertEquals($api, MyParcelComApi::getSingleton());
    }

    /** @test */
    public function testAuthenticate()
    {
        $api = new MyParcelComApi('https://api', $this->client);

        $this->assertEquals(
            $api,
            $api->authenticate($this->authenticator),
            'Api should return itself and not throw an error when a functioning authenticator is used'
        );
    }

    /** @test */
    public function testCreateMinimumViableShipment()
    {
        $recipient = (new Address())
            ->setFirstName('Bobby')
            ->setLastName('Tables')
            ->setCity('Birmingham')
            ->setStreet1('Newbourne Hill')
            ->setStreetNumber(12)
            ->setPostalCode('B48 7QN')
            ->setCountryCode('GB');

        // Minimum required data should be a recipient address and weight. All
        // other data should be filled with defaults.
        $shipment = (new Shipment())
            ->setWeight(500)
            ->setRecipientAddress($recipient);

        $shipment = $this->api->createShipment($shipment);

        $this->assertNull($shipment->getService());
        $this->assertNull($shipment->getContract());
        $this->assertNotNull(
            $shipment->getId(),
            'Once the shipment has been created, it should have an id'
        );
        $this->assertNotNull(
            $shipment->getPrice(),
            'Successfully created shipments should have a price'
        );
        $this->assertEquals(
            $this->api->getDefaultShop()->getReturnAddress(),
            $shipment->getSenderAddress(),
            'The shipment\'s sender address should default to the default shop\'s return address'
        );
        $this->assertEquals(
            $recipient,
            $shipment->getRecipientAddress(),
            'The shipment\'s recipient address should not have changed'
        );
    }

    /** @test */
    public function testSaveShipment()
    {
        $initialAddress = (new Address())
            ->setFirstName('Bobby')
            ->setLastName('Tables')
            ->setCity('Birmingham')
            ->setStreet1('Newbourne Hill')
            ->setStreetNumber(12)
            ->setPostalCode('B48 7QN')
            ->setCountryCode('GB');

        // Minimum required data should be a recipient address and weight. All
        // other data should be filled with defaults.
        $shipment = (new Shipment())
            ->setWeight(500)
            ->setRecipientAddress($initialAddress)
            ->setReturnAddress($initialAddress);

        $shipment = $this->api->saveShipment($shipment);

        $this->assertNull($shipment->getService());
        $this->assertNull($shipment->getContract());
        $this->assertNotNull(
            $shipment->getId(),
            'Once the shipment has been created, it should have an id'
        );
        $this->assertNotNull(
            $shipment->getPrice(),
            'Successfully created shipments should have a price'
        );
        $this->assertEquals(
            $this->api->getDefaultShop()->getReturnAddress(),
            $shipment->getSenderAddress(),
            'The shipment\'s sender address should default to the default shop\'s return address'
        );
        $this->assertEquals(
            $initialAddress,
            $shipment->getRecipientAddress(),
            'The shipment\'s recipient address should not have changed'
        );

        $patchRecipient = (new Address())
            ->setFirstName('Schmidt')
            ->setLastName('Jenko')
            ->setCity('Funkytown')
            ->setStreet1('Jump street')
            ->setStreetNumber(21)
            ->setPostalCode('A48 7QN')
            ->setCountryCode('GB');

        $shipment->setRecipientAddress($patchRecipient);

        // Save an existing shipment should patch it
        $shipment = $this->api->saveShipment($shipment);

        $this->assertEquals(
            $patchRecipient,
            $shipment->getRecipientAddress(),
            'patch did not replace the recipient address'
        );

        $this->assertEquals(
            $initialAddress,
            $shipment->getReturnAddress(),
            'patch should not have replaced the return address'
        );
    }

    /** @test */
    public function testCreateInvalidShipmentMissingRecipient()
    {
        $shipment = new Shipment();

        // Shipments with no recipient and weight, should cause the api to throw an exception.
        $this->expectException(InvalidResourceException::class);
        $this->api->createShipment($shipment);
    }

    /** @test */
    public function testCreateInvalidShipmentMissingWeight()
    {
        $recipient = (new Address())
            ->setFirstName('Bobby')
            ->setLastName('Tables')
            ->setCity('Birmingham')
            ->setStreet1('Newbourne Hill')
            ->setStreetNumber(12)
            ->setPostalCode('B48 7QN')
            ->setCountryCode('GB');
        $shipment = (new Shipment())
            ->setRecipientAddress($recipient);

        // add recipient to test the exception in createShipment
        $this->expectException(InvalidResourceException::class);
        $this->api->createShipment($shipment);
    }

    /** @test */
    public function testGetCarriers()
    {
        $carriers = $this->api->getCarriers();

        $this->assertInstanceOf(CollectionInterface::class, $carriers);
        $this->assertCount(2, $carriers);
        foreach ($carriers as $carrier) {
            $this->assertInstanceOf(CarrierInterface::class, $carrier);
            $this->assertNotEmpty($carrier->getName());
            $this->assertNotEmpty($carrier->getCode());
            $this->assertNotEmpty($carrier->getCredentialsFormat());
        }
    }

    /** @test */
    public function testGetPickUpDropOffLocationsForCarrier()
    {
        $carrier = $this->createMock(CarrierInterface::class);
        $carrier
            ->method('getId')
            ->willReturn('eef00b32-177e-43d3-9b26-715365e4ce46');

        $normalCarrierPudoLocations = $this->api->getPickUpDropOffLocations(
            'GB',
            'B48 7QN',
            null,
            null,
            $carrier
        );

        $this->assertInstanceOf(CollectionInterface::class, $normalCarrierPudoLocations);
        foreach ($normalCarrierPudoLocations as $pudoLocation) {
            $this->assertInstanceOf(PickUpDropOffLocationInterface::class, $pudoLocation);
        }
    }

    /** @test */
    public function testGetPickUpDropOffLocationsForFailingCarrier()
    {
        // This carrier does not have pickup points and thus returns an error.
        // An exception should be thrown.
        $failingCarrier = $this->createMock(CarrierInterface::class);
        $failingCarrier
            ->method('getId')
            ->willReturn('4a78637a-5d81-4e71-9b18-c338968f72fa');

        $this->expectException(RequestException::class);
        $this->api->getPickUpDropOffLocations(
            'GB',
            'B48 7QN',
            null,
            null,
            $failingCarrier,
            false
        );
    }

    /** @test */
    public function testGetPickUpDropOffLocations()
    {
        $carriers = $this->api->getCarriers()->get();

        array_walk($carriers, function (CarrierInterface $carrier) use (&$failingCarrierId, &$normalCarrierId) {
            if ($carrier->getId() === '4a78637a-5d81-4e71-9b18-c338968f72fa') {
                $failingCarrierId = $carrier->getId();
            } elseif ($carrier->getId() === 'eef00b32-177e-43d3-9b26-715365e4ce46') {
                $normalCarrierId = $carrier->getId();
            }
        });

        $allPudoLocations = $this->api->getPickUpDropOffLocations(
            'GB',
            'B48 7QN',
            null,
            null,
            null,
            false
        );

        $this->assertInternalType('array', $allPudoLocations);
        $this->assertNull($allPudoLocations[$failingCarrierId]);
        $this->assertInstanceOf(CollectionInterface::class, $allPudoLocations[$normalCarrierId]);

        foreach ($allPudoLocations[$normalCarrierId] as $pudoLocation) {
            $this->assertInstanceOf(PickUpDropOffLocationInterface::class, $pudoLocation);
        }

        $this->assertCount(count($carriers), $allPudoLocations);
        $this->assertArraySubset(
            array_map(function (CarrierInterface $carrier) {
                return $carrier->getId();
            }, $carriers),
            array_keys($allPudoLocations)
        );
    }

    /** @test */
    public function testItRetrievesPickupLocationsForCarriersWithActiveContract()
    {
        $pudoServices = $this->api->getServices(null, [
            'has_active_contract' => 'true',
            'delivery_method'     => 'pick-up',
        ])->get();
        $this->assertCount(1, $pudoServices);
        $pudoCarrierId = reset($pudoServices)->getCarrier()->getId();

        $allCarriers = $this->api->getCarriers()->get();
        $this->assertCount(2, $allCarriers);

        // Requesting pudo locations without the onlyActiveContracts filter gives pudo locations for both carriers.
        $allPudoLocations = $this->api->getPickUpDropOffLocations(
            'GB',
            'B48 7QN',
            null,
            null,
            null,
            false
        );

        $this->assertTrue(array_key_exists($pudoCarrierId, $allPudoLocations));
        $this->assertCount(2, $allPudoLocations);

        // When requesting pudo locations for active contracts,
        // it only returns the one that has active contract for pudo.
        $filteredPudoLocations = $this->api->getPickUpDropOffLocations('GB', 'B48 7QN');

        $this->assertTrue(array_key_exists($pudoCarrierId, $filteredPudoLocations));
        $this->assertCount(1, $filteredPudoLocations);
    }

    /** @test */
    public function testGetPudoLocationsForSpecificCarrierWhichDoesntHaveActiveContract()
    {
        $pudoServices = $this->api->getServices(null, [
            'has_active_contract' => 'true',
            'delivery_method'     => 'pick-up',
        ])->get();
        $this->assertCount(1, $pudoServices);

        /** @var Carrier $pudoCarrier */
        $pudoCarrier = reset($pudoServices)->getCarrier();

        $pudoLocations = $this->api->getPickUpDropOffLocations(
            'GB',
            'B48 7QN',
            null,
            null,
            $pudoCarrier
        );

        // The carrier with pudo locations should return a set of pudo locations.
        $this->assertNotEmpty($pudoLocations);

        $allCarriers = $this->api->getCarriers()->get();
        $this->assertCount(2, $allCarriers);

        $nonPudoCarriers = array_filter($allCarriers, function (CarrierInterface $carrier) use ($pudoCarrier) {
            return $carrier->getId() !== $pudoCarrier->getId();
        });

        $pudoLocations = $this->api->getPickUpDropOffLocations(
            'GB',
            'B48 7QN',
            null,
            null,
            reset($nonPudoCarriers)
        );

        // The other carrier does not have any pudo services and should thus not return pudo locations.
        $this->assertEmpty($pudoLocations);
    }

    /** @test */
    public function testGetRegions()
    {
        $collection = $this->api->getRegions();
        $allRegions = [];
        for ($offset = 0; $offset < $collection->count(); $offset += 30) {
            $allRegions = array_merge($allRegions, $collection->offset($offset)->get());
        }

        $this->assertInstanceOf(CollectionInterface::class, $collection);
        $this->assertEquals(78, $collection->count());
        $this->assertCount(78, $allRegions);

        array_walk($allRegions, function ($region) {
            $this->assertInstanceOf(RegionInterface::class, $region);
        });
    }

    /** @test */
    public function testGetGbRegions()
    {
        $regions = $this->api->getRegions([
            'country_code' => 'GB',
        ]);

        $this->assertInstanceOf(CollectionInterface::class, $regions);
        $this->assertEquals(9, $regions->count());
        foreach ($regions as $region) {
            $this->assertInstanceOf(RegionInterface::class, $region);
            $this->assertEquals('GB', $region->getCountryCode());
        }

        $ireland = $this->api->getRegions([
            'country_code' => 'GB',
            'region_code'  => 'NIR',
        ]);

        $this->assertInstanceOf(CollectionInterface::class, $ireland);
        $this->assertEquals(1, $ireland->count());
        foreach ($ireland as $region) {
            $this->assertInstanceOf(RegionInterface::class, $region);
            $this->assertEquals('GB', $region->getCountryCode());
            $this->assertEquals('NIR', $region->getRegionCode());
        }
    }

    /** @test */
    public function testGetGbRegionsWithBackwardCompatibility()
    {
        $regions = $this->api->getRegions('GB');

        $this->assertInstanceOf(CollectionInterface::class, $regions);
        $this->assertEquals(9, $regions->count());
        foreach ($regions as $region) {
            $this->assertInstanceOf(RegionInterface::class, $region);
            $this->assertEquals('GB', $region->getCountryCode());
        }

        $ireland = $this->api->getRegions('GB', 'NIR');

        $this->assertInstanceOf(CollectionInterface::class, $ireland);
        $this->assertEquals(1, $ireland->count());
        foreach ($ireland as $region) {
            $this->assertInstanceOf(RegionInterface::class, $region);
            $this->assertEquals('GB', $region->getCountryCode());
            $this->assertEquals('NIR', $region->getRegionCode());
        }
    }

    /** @test */
    public function testGetRegionsWithNonExistingRegionCode()
    {
        $regions = $this->api->getRegions([
            'country_code' => 'NL',
            'region_code'  => 'NH',
        ]);

        $this->assertInstanceOf(CollectionInterface::class, $regions);
        $this->assertEquals(1, $regions->count());
        foreach ($regions as $region) {
            $this->assertInstanceOf(RegionInterface::class, $region);
            $this->assertEquals('NL', $region->getCountryCode());
        }
    }

    /** @test */
    public function testGetRegionsWithNonExistingRegionCodeWithBackwardCompatibility()
    {
        $regions = $this->api->getRegions('NL', 'NH');

        $this->assertInstanceOf(CollectionInterface::class, $regions);
        $this->assertEquals(1, $regions->count());
        foreach ($regions as $region) {
            $this->assertInstanceOf(RegionInterface::class, $region);
            $this->assertEquals('NL', $region->getCountryCode());
        }
    }

    /** @test */
    public function testItFiltersRegionsByPostalCode()
    {
        $regions = $this->api->getRegions([
            'country_code' => 'GB',
            'postal_code'  => 'NW1 6XE',
        ]);

        $this->assertInstanceOf(CollectionInterface::class, $regions);
        $this->assertEquals(1, $regions->count());
        foreach ($regions as $region) {
            $this->assertInstanceOf(RegionInterface::class, $region);
            $this->assertEquals('GB', $region->getCountryCode());
        }
    }

    /** @test */
    public function testGetServices()
    {
        $services = $this->api->getServices();

        $this->assertInstanceOf(CollectionInterface::class, $services);
        foreach ($services as $service) {
            $this->assertInstanceOf(ServiceInterface::class, $service);
        }
    }

    /** @test */
    public function testGetServicesForShipment()
    {
        $recipient = (new Address())
            ->setFirstName('Bobby')
            ->setLastName('Tables')
            ->setCity('Birmingham')
            ->setStreet1('Newbourne Hill')
            ->setStreetNumber(12)
            ->setPostalCode('B48 7QN')
            ->setCountryCode('GB');
        $shipment = (new Shipment())
            ->setWeight(500)
            ->setRecipientAddress($recipient);

        $services = $this->api->getServices($shipment);

        $this->assertInstanceOf(CollectionInterface::class, $services);
        foreach ($services as $service) {
            $this->assertInstanceOf(ServiceInterface::class, $service);
        }
    }

    /** @test */
    public function testGetServicesForCarrier()
    {
        $carriers = $this->api->getCarriers();

        foreach ($carriers as $carrier) {
            $services = $this->api->getServicesForCarrier($carrier);

            foreach ($services as $service) {
                $this->assertInstanceOf(ServiceInterface::class, $service);
                $this->assertEquals($carrier->getId(), $service->getCarrier()->getId());
            }
        }
    }

    /** @test */
    public function testItRetrievesServiceRates()
    {
        $serviceRates = $this->api->getServiceRates();

        $this->assertInstanceOf(CollectionInterface::class, $serviceRates);
        foreach ($serviceRates as $serviceRate) {
            $this->assertInstanceOf(ServiceRateInterface::class, $serviceRate);
        }
    }

    /** @test */
    public function testItRetrievesServiceRatesForAService()
    {
        $services = $this->api->getServices()->get();
        $service = reset($services);

        $serviceRates = $service->getServiceRates();
        foreach ($serviceRates as $serviceRate) {
            $this->assertInstanceOf(ServiceRateInterface::class, $serviceRate);
        }
    }

    /** @test */
    public function testItRetrievesServiceRatesForShipment()
    {
        $recipient = (new Address())
            ->setFirstName('Hank')
            ->setLastName('Mozzy')
            ->setCity('London')
            ->setStreet1('Allen Street')
            ->setStreetNumber(1)
            ->setPostalCode('W8 6UX')
            ->setCountryCode('GB');

        $shipment = (new Shipment())
            ->setWeight(500)
            ->setRecipientAddress($recipient);

        $serviceRates = $this->api->getServiceRatesForShipment($shipment);
        $this->assertInstanceOf(CollectionInterface::class, $serviceRates);
        foreach ($serviceRates as $serviceRate) {
            $this->assertInstanceOf(ServiceRateInterface::class, $serviceRate);
            $this->assertGreaterThanOrEqual(500, $serviceRate->getWeightMax());
            $this->assertLessThanOrEqual(500, $serviceRate->getWeightMin());
        }
    }

    /** @test */
    public function testGetShipments()
    {
        $shipments = $this->api->getShipments();

        $this->assertInstanceOf(CollectionInterface::class, $shipments);
        foreach ($shipments as $shipment) {
            $this->assertInstanceOf(ShipmentInterface::class, $shipment);
        }
    }

    /** @test */
    public function testGetShipmentsForShop()
    {
        $shops = $this->api->getShops();

        foreach ($shops as $shop) {
            $shipments = $this->api->getShipments($shop);

            $this->assertInstanceOf(CollectionInterface::class, $shipments);
            foreach ($shipments as $shipment) {
                $this->assertInstanceOf(ShipmentInterface::class, $shipment);
                $this->assertEquals($shop->getId(), $shipment->getShop()->getId());
                $this->assertEquals($shop->getType(), $shipment->getShop()->getType());
                $this->assertEquals($shop->getCreatedAt(), $shipment->getShop()->getCreatedAt());
                $this->assertEquals($shop->getBillingAddress(), $shipment->getShop()->getBillingAddress());
                $this->assertEquals($shop->getReturnAddress(), $shipment->getShop()->getReturnAddress());
                $this->assertEquals($shop->getName(), $shipment->getShop()->getName());
            }
        }
    }

    /** @test */
    public function testGetShipment()
    {
        $shipments = $this->api->getShipments();

        foreach ($shipments as $shipment) {
            $this->assertEquals($shipment, $this->api->getShipment($shipment->getId()));
        }
    }

    /** @test */
    public function testGetShipmentStatus()
    {
        $shipment = $this->api->getShipment('shipment-id-1');

        $shipmentStatus = $shipment->getShipmentStatus();
        $carrierStatuses = $shipmentStatus->getCarrierStatuses();

        $this->assertInstanceOf(ShipmentStatusInterface::class, $shipmentStatus);
        $this->assertCount(1, $carrierStatuses);
        $this->assertEquals('9001', $carrierStatuses[0]->getCode());
        $this->assertEquals('Confirmed at destination', $carrierStatuses[0]->getDescription());
        $this->assertEquals(1504801719, $carrierStatuses[0]->getAssignedAt()->getTimestamp());

        $status = $shipmentStatus->getStatus();

        $this->assertInstanceOf(StatusInterface::class, $status);
        $this->assertEquals('shipment-delivered', $status->getCode());
        $this->assertEquals('success', $status->getLevel());
        $this->assertEquals('Delivered', $status->getName());
        $this->assertEquals('The shipment has been delivered', $status->getDescription());
    }

    public function testGetShipmentStatusHistory()
    {
        $shipment = $this->api->getShipment('shipment-id-1');

        $shipmentStatuses = $shipment->getStatusHistory();
        $this->assertCount(5, $shipmentStatuses);

        $shipmentStatus = $shipmentStatuses[0];
        $carrierStatuses = $shipmentStatus->getCarrierStatuses();
        $this->assertInstanceOf(ShipmentStatusInterface::class, $shipmentStatus);
        $this->assertEquals(1504801719, $shipmentStatus->getCreatedAt()->getTimestamp());
        $this->assertCount(1, $carrierStatuses);
        $this->assertEquals('9001', $carrierStatuses[0]->getCode());
        $this->assertEquals('Confirmed at destination', $carrierStatuses[0]->getDescription());
        $this->assertEquals(1504801719, $carrierStatuses[0]->getAssignedAt()->getTimestamp());

        $status = $shipmentStatus->getStatus();
        $this->assertInstanceOf(StatusInterface::class, $status);
        $this->assertEquals('shipment-delivered', $status->getCode());
        $this->assertEquals('success', $status->getLevel());
        $this->assertEquals('Delivered', $status->getName());
        $this->assertEquals('The shipment has been delivered', $status->getDescription());

        $shipmentStatus = $shipmentStatuses[1];
        $carrierStatuses = $shipmentStatus->getCarrierStatuses();
        $this->assertInstanceOf(ShipmentStatusInterface::class, $shipmentStatus);
        $this->assertEquals(1504701719, $shipmentStatus->getCreatedAt()->getTimestamp());
        $this->assertCount(2, $carrierStatuses);
        $this->assertEquals('4567-1', $carrierStatuses[0]->getCode());
        $this->assertEquals('Delivery moved a couple of meters', $carrierStatuses[0]->getDescription());
        $this->assertEquals(1504701750, $carrierStatuses[0]->getAssignedAt()->getTimestamp());
        $this->assertEquals('4567', $carrierStatuses[1]->getCode());
        $this->assertEquals('Delivery on it\'s way', $carrierStatuses[1]->getDescription());
        $this->assertEquals(1504701719, $carrierStatuses[1]->getAssignedAt()->getTimestamp());

        $status = $shipmentStatus->getStatus();
        $this->assertInstanceOf(StatusInterface::class, $status);
        $this->assertEquals('shipment-with-courier', $status->getCode());
        $this->assertEquals('success', $status->getLevel());
        $this->assertEquals('At courier', $status->getName());
        $this->assertEquals('The shipment is at the courier', $status->getDescription());

        $shipmentStatus = $shipmentStatuses[2];
        $carrierStatuses = $shipmentStatus->getCarrierStatuses();
        $this->assertInstanceOf(ShipmentStatusInterface::class, $shipmentStatus);
        $this->assertEquals(1504501720, $shipmentStatus->getCreatedAt()->getTimestamp());
        $this->assertCount(1, $carrierStatuses);
        $this->assertEquals('10001', $carrierStatuses[0]->getCode());
        $this->assertEquals('Parcel received', $carrierStatuses[0]->getDescription());
        $this->assertEquals(1504501719, $carrierStatuses[0]->getAssignedAt()->getTimestamp());

        $status = $shipmentStatus->getStatus();
        $this->assertInstanceOf(StatusInterface::class, $status);
        $this->assertEquals('shipment-received-by-carrier', $status->getCode());
        $this->assertEquals('success', $status->getLevel());
        $this->assertEquals('At carrier', $status->getName());
        $this->assertEquals('The shipment is at the carrier', $status->getDescription());

        $shipmentStatus = $shipmentStatuses[3];
        $carrierStatuses = $shipmentStatus->getCarrierStatuses();
        $this->assertInstanceOf(ShipmentStatusInterface::class, $shipmentStatus);
        $this->assertEquals(1504101722, $shipmentStatus->getCreatedAt()->getTimestamp());
        $this->assertCount(1, $carrierStatuses);
        $this->assertNull($carrierStatuses[0]->getCode());
        $this->assertNull($carrierStatuses[0]->getDescription());
        $this->assertEquals(1504101719, $carrierStatuses[0]->getAssignedAt()->getTimestamp());

        $status = $shipmentStatus->getStatus();
        $this->assertInstanceOf(StatusInterface::class, $status);
        $this->assertEquals('shipment-registered', $status->getCode());
        $this->assertEquals('success', $status->getLevel());
        $this->assertEquals('Registered', $status->getName());
        $this->assertEquals('The shipment has been registered at the carrier', $status->getDescription());

        $shipmentStatus = $shipmentStatuses[4];
        $carrierStatuses = $shipmentStatus->getCarrierStatuses();
        $this->assertInstanceOf(ShipmentStatusInterface::class, $shipmentStatus);
        $this->assertEquals(1504101718, $shipmentStatus->getCreatedAt()->getTimestamp());
        $this->assertCount(1, $carrierStatuses);
        $this->assertNull($carrierStatuses[0]->getCode());
        $this->assertNull($carrierStatuses[0]->getDescription());
        $this->assertEquals(1504101718, $carrierStatuses[0]->getAssignedAt()->getTimestamp());

        $status = $shipmentStatus->getStatus();
        $this->assertInstanceOf(StatusInterface::class, $status);
        $this->assertEquals('shipment-concept', $status->getCode());
        $this->assertEquals('concept', $status->getLevel());
        $this->assertEquals('Concept', $status->getName());
        $this->assertEquals('The shipment is a concept', $status->getDescription());
    }

    /** @test */
    public function testGetShops()
    {
        $shops = $this->api->getShops();

        $this->assertInstanceOf(CollectionInterface::class, $shops);
        foreach ($shops as $shop) {
            $this->assertInstanceOf(ShopInterface::class, $shop);
        }
    }

    /** @test */
    public function testGetDefaultShop()
    {
        $shop = $this->api->getDefaultShop();
        $this->assertInstanceOf(ShopInterface::class, $shop);
    }

    /** @test */
    public function testGetResourceById()
    {
        /** @var FileInterface $file */
        $file = $this->api->getResourceById(ResourceInterface::TYPE_FILE, 'file-id-1');
        $this->assertInstanceOf(FileInterface::class, $file);
        $this->assertEquals('files', $file->getType());
        $this->assertEquals('file-id-1', $file->getId());
        $this->assertEquals('label', $file->getDocumentType());
        $this->assertEquals([['extension' => 'pdf', 'mime_type' => 'application/pdf']], $file->getFormats());

        /** @var ShopInterface $shop */
        $shop = $this->api->getResourceById(ResourceInterface::TYPE_SHOP, 'shop-id-1');
        $this->assertInstanceOf(ShopInterface::class, $shop);
        $this->assertEquals('shops', $shop->getType());
        $this->assertEquals('shop-id-1', $shop->getId());
        $this->assertEquals('Testshop', $shop->getName());
        $this->assertEquals((new \DateTime())->setTimestamp(1509378904), $shop->getCreatedAt());
        $this->assertEquals(
            (new Address())
                ->setStreet1('Hoofdweg')
                ->setStreetNumber(679)
                ->setPostalCode('1AA BB2')
                ->setCity('London')
                ->setCountryCode('GB')
                ->setFirstName('Mister')
                ->setLastName('Billing')
                ->setCompany('MyParcel.com')
                ->setEmail('info@myparcel.com')
                ->setPhoneNumber('+31 85 208 5997'),
            $shop->getBillingAddress()
        );
        $this->assertEquals(
            (new Address())
                ->setStreet1('Hoofdweg')
                ->setStreetNumber(679)
                ->setPostalCode('1AA BB2')
                ->setCity('London')
                ->setCountryCode('GB')
                ->setFirstName('Mister')
                ->setLastName('Return')
                ->setCompany('MyParcel.com')
                ->setEmail('info@myparcel.com')
                ->setPhoneNumber('+31 85 208 5997'),
            $shop->getReturnAddress()
        );
    }
}
