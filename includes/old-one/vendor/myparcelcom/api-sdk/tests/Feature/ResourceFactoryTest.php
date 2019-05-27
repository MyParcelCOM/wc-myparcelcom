<?php

namespace MyParcelCom\ApiSdk\Tests\Feature;

use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\PickUpDropOffLocationInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\RegionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceGroupInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInsuranceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionPriceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;
use MyParcelCom\ApiSdk\Resources\ResourceFactory;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;

class ResourceFactoryTest extends TestCase
{
    use MocksApiCommunication;

    /** @test */
    public function testCreateEmptyCarrier()
    {
        $resourceFactory = new ResourceFactory();
        $carrier = $resourceFactory->create('carriers');

        $this->assertInstanceOf(CarrierInterface::class, $carrier);
        $this->assertEquals([
            'type' => 'carriers',
        ], $carrier->jsonSerialize());
    }

    /** @test */
    public function testCreateCarrier()
    {
        $carrierAttributes = [
            'name' => 'MyParcel.com Carrier',
        ];

        $resourceFactory = new ResourceFactory();
        $carrier = $resourceFactory->create('carriers', $carrierAttributes);

        $this->assertInstanceOf(CarrierInterface::class, $carrier);
        $this->assertEquals([
            'type'       => 'carriers',
            'attributes' => $carrierAttributes,
        ], $carrier->jsonSerialize());
    }

    public function testCreateCarrierConract()
    {
        $resourceFactory = new ResourceFactory();
        $carrierContract = $resourceFactory->create(
            'carrier-contracts',
            [
                'id'                => 'carrier-id',
                'currency'          => 'JPY',
                'carrier'           => [
                    'type' => 'carriers',
                    'id'   => 'carrier-id',
                ],
                'service_contracts' => [
                    [
                        'type' => 'service-contracts',
                        'id'   => 'service-contract-id-1',
                    ],
                    [
                        'type' => 'service-contracts',
                        'id'   => 'service-contract-id-2',
                    ],
                    [
                        'type' => 'service-contracts',
                        'id'   => 'service-contract-id-3',
                    ],
                ],
            ]
        );

        $this->assertInstanceOf(CarrierContractInterface::class, $carrierContract);
        $this->assertEquals([
            'id'            => 'carrier-id',
            'type'          => 'carrier-contracts',
            'attributes'    => [
                'currency' => 'JPY',
            ],
            'relationships' => [
                'carrier'           => [
                    'data' => [
                        'type' => 'carriers',
                        'id'   => 'carrier-id',
                    ],
                ],
                'service_contracts' => [
                    'data' => [
                        [
                            'type' => 'service-contracts',
                            'id'   => 'service-contract-id-1',
                        ],
                        [
                            'type' => 'service-contracts',
                            'id'   => 'service-contract-id-2',
                        ],
                        [
                            'type' => 'service-contracts',
                            'id'   => 'service-contract-id-3',
                        ],
                    ],
                ],
            ],
        ], $carrierContract->jsonSerialize());
    }

    /** @test */
    public function testCreateEmptyContract()
    {
        $resourceFactory = new ResourceFactory();
        $contract = $resourceFactory->create('service-contracts');

        $this->assertInstanceOf(ServiceContractInterface::class, $contract);
        $this->assertEquals([
            'type' => 'service-contracts',
        ], $contract->jsonSerialize());
    }

    /** @test */
    public function testCreateServiceContract()
    {
        $contractAttributes = [
            'service'               => [
                'type' => 'services',
                'id'   => 'service-id',
            ],
            'carrier_contract'      => [
                'type' => 'carrier-contracts',
                'id'   => 'carrier-contract-id',
            ],
            'service_groups'        => [
                [
                    'type' => 'service-groups',
                    'id'   => 'service-group-id',
                ],
            ],
            'service_option_prices' => [
                [
                    'type' => 'service-option-prices',
                    'id'   => 'service-option-price-id',
                ],
            ],
            'service_insurances'    => [
                [
                    'type' => 'service-insurances',
                    'id'   => 'service-insurance-id',
                ],
            ],
        ];

        $resourceFactory = new ResourceFactory();
        $contract = $resourceFactory->create('service-contracts', $contractAttributes);

        $this->assertInstanceOf(ServiceContractInterface::class, $contract);
        $this->assertEquals([
            'type'          => 'service-contracts',
            'relationships' => [
                'service'               => [
                    'data' => [
                        'type' => 'services',
                        'id'   => 'service-id',
                    ],
                ],
                'carrier_contract'      => [
                    'data' => [
                        'type' => 'carrier-contracts',
                        'id'   => 'carrier-contract-id',
                    ],
                ],
                'service_groups'        => [
                    'data' => [
                        [
                            'type' => 'service-groups',
                            'id'   => 'service-group-id',
                        ],
                    ],
                ],
                'service_option_prices' => [
                    'data' => [
                        [
                            'type' => 'service-option-prices',
                            'id'   => 'service-option-price-id',
                        ],
                    ],
                ],
                'service_insurances'    => [
                    'data' => [
                        [
                            'type' => 'service-insurances',
                            'id'   => 'service-insurance-id',
                        ],
                    ],
                ],
            ],
        ], $contract->jsonSerialize());
    }

    /** @test */
    public function testCreateEmptyPickUpDropOffLocation()
    {
        $resourceFactory = new ResourceFactory();
        $pudoLocation = $resourceFactory->create('pickup-dropoff-locations');

        $this->assertInstanceOf(PickUpDropOffLocationInterface::class, $pudoLocation);
        $this->assertEquals([
            'type' => 'pickup-dropoff-locations',
        ], $pudoLocation->jsonSerialize());
    }

    /** @test */
    public function testCreatePickUpDropOffLocation()
    {
        $pudoAttributes = [
            'address'       => [
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment A',
                'street_number'        => '40',
                'street_number_suffix' => 'A',
                'postal_code'          => '1AR BR2',
                'city'                 => 'London',
                'region_code'          => 'NH',
                'country_code'         => 'AF',
                'first_name'           => 'Robert',
                'last_name'            => 'Drop Tables',
                'company'              => 'ACME co.',
                'email'                => 'rob@tables.com',
                'phone_number'         => '+31 (0)234 567 890',
            ],
            'opening_hours' => [
                [
                    'day'    => 'Monday',
                    'open'   => '12:00',
                    'closed' => '15:00',
                ],
                [
                    'day'    => 'Tuesday',
                    'open'   => '09:00',
                    'closed' => '19:00',
                ],
                [
                    'day'    => 'Wednesday',
                    'open'   => '09:00',
                    'closed' => '19:00',
                ],
                [
                    'day'    => 'Thursday',
                    'open'   => '09:00',
                    'closed' => '19:00',
                ],
                [
                    'day'    => 'Friday',
                    'open'   => '09:00',
                    'closed' => '19:00',
                ],
                [
                    'day'    => 'Saturday',
                    'open'   => '10:00',
                    'closed' => '17:00',
                ],
                [
                    'day'    => 'Sunday',
                    'open'   => '00:00',
                    'closed' => '00:00',
                ],
            ],
            'position'      => [
                'latitude'  => 1.2345,
                'longitude' => 2.34567,
                'distance'  => 5000,
            ],
        ];

        $resourceFactory = new ResourceFactory();
        $pudoLocation = $resourceFactory->create('pickup-dropoff-locations', $pudoAttributes);

        $this->assertInstanceOf(PickUpDropOffLocationInterface::class, $pudoLocation);
        $this->assertEquals([
            'type'       => 'pickup-dropoff-locations',
            'attributes' => $pudoAttributes,
        ], $pudoLocation->jsonSerialize());
    }

    /** @test */
    public function testCreateEmptyRegion()
    {
        $resourceFactory = new ResourceFactory();
        $region = $resourceFactory->create('regions');

        $this->assertInstanceOf(RegionInterface::class, $region);
        $this->assertEquals([
            'type' => 'regions',
        ], $region->jsonSerialize());
    }

    /** @test */
    public function testCreateRegion()
    {
        $regionAttributes = [
            'country_code' => 'NL',
            'region_code'  => 'ZH',
            'currency'     => 'EUR',
            'name'         => 'Rotterdam',
        ];

        $resourceFactory = new ResourceFactory();
        $region = $resourceFactory->create('regions', $regionAttributes);

        $this->assertInstanceOf(RegionInterface::class, $region);
        $this->assertEquals([
            'type'       => 'regions',
            'attributes' => $regionAttributes,
        ], $region->jsonSerialize());
    }

    /** @test */
    public function testCreateEmptyService()
    {
        $resourceFactory = new ResourceFactory();
        $service = $resourceFactory->create('services');

        $this->assertInstanceOf(ServiceInterface::class, $service);
        $this->assertEquals([
            'type' => 'services',
        ], $service->jsonSerialize());
    }

    /** @test */
    public function testCreateService()
    {
        $resourceFactory = new ResourceFactory();
        $service = $resourceFactory->create('services', [
            'id'              => 'service-id-9001',
            'name'            => 'Easy Delivery Service',
            'package_type'    => ServiceInterface::PACKAGE_TYPE_PARCEL,
            'transit_time'    => [
                'min' => 2,
                'max' => 5,
            ],
            'handover_method' => 'drop-off',
            'delivery_days'   => [
                'Monday',
                'Wednesday',
                'Friday',
            ],
            'delivery_method' => 'delivery',
            'carrier'         => [
                'id' => 'carrier-id-1',
            ],
            'region_from'     => [
                'id' => 'region-id-1',
            ],
            'region_to'       => [
                'id' => 'region-id-2',
            ],
        ]);

        $this->assertInstanceOf(ServiceInterface::class, $service);
        $this->assertEquals([
            'id'            => 'service-id-9001',
            'type'          => 'services',
            'attributes'    => [
                'name'            => 'Easy Delivery Service',
                'package_type'    => ServiceInterface::PACKAGE_TYPE_PARCEL,
                'transit_time'    => [
                    'min' => 2,
                    'max' => 5,
                ],
                'handover_method' => 'drop-off',
                'delivery_days'   => [
                    'Monday',
                    'Wednesday',
                    'Friday',
                ],
                'delivery_method' => 'delivery',
            ],
            'relationships' => [
                'carrier'     => [
                    'data' => [
                        'id'   => 'carrier-id-1',
                        'type' => 'carriers',
                    ],
                ],
                'region_from' => [
                    'data' => [
                        'id'   => 'region-id-1',
                        'type' => 'regions',
                    ],
                ],
                'region_to'   => [
                    'data' => [
                        'id'   => 'region-id-2',
                        'type' => 'regions',
                    ],
                ],
            ],
        ], $service->jsonSerialize());
    }

    /** @test */
    public function testCreateEmptyServiceGroup()
    {
        $resourceFactory = new ResourceFactory();
        $serviceGroup = $resourceFactory->create('service-groups');

        $this->assertInstanceOf(ServiceGroupInterface::class, $serviceGroup);
        $this->assertEquals([
            'type' => 'service-groups',
        ], $serviceGroup->jsonSerialize());
    }

    /** @test */
    public function testCreateServiceGroup()
    {
        $resourceFactory = new ResourceFactory();
        $serviceGroup = $resourceFactory->create('service-groups', [
            'price'      => 741,
            'currency'   => 'GBP',
            'step_price' => 10,
            'step_size'  => 10,
            'weight_max' => 987,
            'weight_min' => 123,
        ]);

        $this->assertInstanceOf(ServiceGroupInterface::class, $serviceGroup);
        $this->assertEquals([
            'type'       => 'service-groups',
            'attributes' => [
                'price'      => [
                    'amount'   => 741,
                    'currency' => 'GBP',
                ],
                'step_price' => [
                    'amount'   => 10,
                    'currency' => 'GBP',
                ],
                'step_size'  => 10,
                'weight'     => [
                    'max' => 987,
                    'min' => 123,
                ],
            ],
        ], $serviceGroup->jsonSerialize());
    }

    /** @test */
    public function testCreateEmptyServiceOption()
    {
        $resourceFactory = new ResourceFactory();
        $serviceOption = $resourceFactory->create('service-options');

        $this->assertInstanceOf(ServiceOptionInterface::class, $serviceOption);
        $this->assertEquals([
            'type' => 'service-options',
        ], $serviceOption->jsonSerialize());
    }

    /** @test */
    public function testCreateServiceOption()
    {
        $resourceFactory = new ResourceFactory();
        $serviceOption = $resourceFactory->create('service-options', [
            'name'     => 'Sign on delivery',
            'code'     => 'some-code',
            'category' => 'some-category',
        ]);

        $this->assertInstanceOf(ServiceOptionInterface::class, $serviceOption);
        $this->assertEquals([
            'type'       => 'service-options',
            'attributes' => [
                'name'     => 'Sign on delivery',
                'code'     => 'some-code',
                'category' => 'some-category',
            ],
        ], $serviceOption->jsonSerialize());
    }

    /** @test */
    public function testCreateServiceOptionPrice()
    {
        $resourceFactory = new ResourceFactory();
        $serviceOption = $resourceFactory->create('service-option-prices', [
            'price'            => 55,
            'currency'         => 'NOK',
            'service_option'   => [
                'id'   => 'service-option-id',
                'type' => 'service-options',
            ],
            'service_contract' => [
                'id'   => 'service-contract-id',
                'type' => 'service-contracts',
            ],
            'required'         => true,
        ]);

        $this->assertInstanceOf(ServiceOptionPriceInterface::class, $serviceOption);
        $this->assertEquals([
            'type'          => 'service-option-prices',
            'attributes'    => [
                'price'    => [
                    'amount'   => 55,
                    'currency' => 'NOK',
                ],
                'required' => true,
            ],
            'relationships' => [
                'service_option'   => [
                    'data' => [
                        'id'   => 'service-option-id',
                        'type' => 'service-options',
                    ],
                ],
                'service_contract' => [
                    'data' => [
                        'id'   => 'service-contract-id',
                        'type' => 'service-contracts',
                    ],
                ],
            ],
        ], $serviceOption->jsonSerialize());
    }

    /** @test */
    public function testCreateEmptyServiceInsurance()
    {
        $resourceFactory = new ResourceFactory();
        $serviceInsurance = $resourceFactory->create('service-insurances');

        $this->assertInstanceOf(ServiceInsuranceInterface::class, $serviceInsurance);
        $this->assertEquals([
            'type' => 'service-insurances',
        ], $serviceInsurance->jsonSerialize());
    }

    /** @test */
    public function testCreateServiceInsurance()
    {
        $resourceFactory = new ResourceFactory();
        $serviceInsurance = $resourceFactory->create('service-insurances', [
            'covered' => [
                'amount'   => 10000,
                'currency' => 'EUR',
            ],
            'price'   => [
                'amount'   => 500,
                'currency' => 'EUR',
            ],
        ]);

        $this->assertInstanceOf(ServiceInsuranceInterface::class, $serviceInsurance);
        $this->assertEquals([
            'type'       => 'service-insurances',
            'attributes' => [
                'covered' => [
                    'amount'   => 10000,
                    'currency' => 'EUR',
                ],
                'price'   => [
                    'amount'   => 500,
                    'currency' => 'EUR',
                ],
            ],
        ], $serviceInsurance->jsonSerialize());
    }

    /** @test */
    public function testCreateEmptyShipment()
    {
        $resourceFactory = new ResourceFactory();
        $shipment = $resourceFactory->create('shipments');

        $this->assertInstanceOf(ShipmentInterface::class, $shipment);
        $this->assertEquals([
            'type' => 'shipments',
        ], $shipment->jsonSerialize());
    }

    /** @test */
    public function testCreateShipment()
    {
        $resourceFactory = new ResourceFactory();
        $shipment = $resourceFactory->create('shipments', [
            'barcode'                      => 'S3BARCODE',
            'description'                  => 'order #012ASD',
            'price'                        => [
                'amount'   => 99,
                'currency' => 'USD',
            ],
            'insurance'                    => [
                'amount'   => 50,
                'currency' => 'USD',
            ],
            'physical_properties'          => [
                'weight' => 1000,
                'length' => 1100,
                'height' => 1300,
                'width'  => 1400,
                'volume' => 2002,
            ],
            'physical_properties_verified' => [
                'weight' => 100,
                'length' => 110,
                'height' => 130,
                'width'  => 140,
                'volume' => 2.002,
            ],
            'recipient_address'            => [
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment 4',
                'street_number'        => '1',
                'street_number_suffix' => 'A',
                'postal_code'          => '1AR BR2',
                'city'                 => 'London',
                'region_code'          => 'NH',
                'country_code'         => 'AF',
                'first_name'           => 'Robert',
                'last_name'            => 'Drop Tables',
                'company'              => 'ACME co.',
                'email'                => 'rob@tables.com',
                'phone_number'         => '+31 (0)234 567 890',
            ],
            'sender_address'               => [
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment 9',
                'street_number'        => '4',
                'street_number_suffix' => 'A',
                'postal_code'          => '1AR BR2',
                'city'                 => 'London',
                'region_code'          => 'NH',
                'country_code'         => 'AF',
                'first_name'           => 'Robert',
                'last_name'            => 'Drop Tables',
                'company'              => 'ACME co.',
                'email'                => 'rob@tables.com',
                'phone_number'         => '+31 (0)234 567 890',
            ],
            'pickup_location_code'         => 'CODE123',
            'pickup_location_address'      => [
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment 41',
                'street_number'        => '2',
                'street_number_suffix' => 'A',
                'postal_code'          => '1AR BR2',
                'city'                 => 'London',
                'region_code'          => 'NH',
                'country_code'         => 'AF',
                'first_name'           => 'Robert',
                'last_name'            => 'Drop Tables',
                'company'              => 'ACME co.',
                'email'                => 'rob@tables.com',
                'phone_number'         => '+31 (0)234 567 890',
            ],
            'register_at'                  => 1526913941,
            'shop'                         => ['id' => 'shop-id-1', 'type' => 'shops'],
            'service_contract'             => ['id' => 'service-contract-id-1', 'type' => 'service-contracts'],
            'contract'                     => ['id' => 'contract-id-1', 'type' => 'contracts'],
            'shipment_status'              => ['id' => 'shipment-status-id-1', 'type' => 'shipment-statuses'],
            'service_options'              => [['id' => 'option-id-1', 'type' => 'service-options']],
            'files'                        => [['id' => 'file-id-1', 'type' => 'files']],
        ]);

        $this->assertInstanceOf(ShipmentInterface::class, $shipment);
        $this->assertEquals([
            'type'          => 'shipments',
            'attributes'    => [
                'barcode'                      => 'S3BARCODE',
                'description'                  => 'order #012ASD',
                'price'                        => [
                    'amount'   => 99,
                    'currency' => 'USD',
                ],
                'insurance'                    => [
                    'amount'   => 50,
                    'currency' => 'USD',
                ],
                'physical_properties'          => [
                    'weight' => 1000,
                    'length' => 1100,
                    'height' => 1300,
                    'width'  => 1400,
                    'volume' => 2002,
                ],
                'physical_properties_verified' => [
                    'weight' => 100,
                    'length' => 110,
                    'height' => 130,
                    'width'  => 140,
                    'volume' => 2.002,
                ],
                'recipient_address'            => [
                    'street_1'             => 'Diagonally',
                    'street_2'             => 'Apartment 4',
                    'street_number'        => '1',
                    'street_number_suffix' => 'A',
                    'postal_code'          => '1AR BR2',
                    'city'                 => 'London',
                    'region_code'          => 'NH',
                    'country_code'         => 'AF',
                    'first_name'           => 'Robert',
                    'last_name'            => 'Drop Tables',
                    'company'              => 'ACME co.',
                    'email'                => 'rob@tables.com',
                    'phone_number'         => '+31 (0)234 567 890',
                ],
                'sender_address'               => [
                    'street_1'             => 'Diagonally',
                    'street_2'             => 'Apartment 9',
                    'street_number'        => '4',
                    'street_number_suffix' => 'A',
                    'postal_code'          => '1AR BR2',
                    'city'                 => 'London',
                    'region_code'          => 'NH',
                    'country_code'         => 'AF',
                    'first_name'           => 'Robert',
                    'last_name'            => 'Drop Tables',
                    'company'              => 'ACME co.',
                    'email'                => 'rob@tables.com',
                    'phone_number'         => '+31 (0)234 567 890',
                ],
                'pickup_location'              => [
                    'code'    => 'CODE123',
                    'address' => [
                        'street_1'             => 'Diagonally',
                        'street_2'             => 'Apartment 41',
                        'street_number'        => '2',
                        'street_number_suffix' => 'A',
                        'postal_code'          => '1AR BR2',
                        'city'                 => 'London',
                        'region_code'          => 'NH',
                        'country_code'         => 'AF',
                        'first_name'           => 'Robert',
                        'last_name'            => 'Drop Tables',
                        'company'              => 'ACME co.',
                        'email'                => 'rob@tables.com',
                        'phone_number'         => '+31 (0)234 567 890',
                    ],
                ],
                'register_at'                  => 1526913941,
            ],
            'relationships' => [
                'shop'             => ['data' => ['id' => 'shop-id-1', 'type' => 'shops']],
                'service_contract' => ['data' => ['id' => 'service-contract-id-1', 'type' => 'service-contracts']],
                'shipment_status'  => ['data' => ['id' => 'shipment-status-id-1', 'type' => 'shipment-statuses']],
                'service_options'  => ['data' => [['id' => 'option-id-1', 'type' => 'service-options']]],
                'files'            => ['data' => [['id' => 'file-id-1', 'type' => 'files']]],
            ],
        ], $shipment->jsonSerialize());
    }

    /** @test */
    public function testCreateShipmentWithCustoms()
    {
        $resourceFactory = new ResourceFactory();
        $shipment = $resourceFactory->create('shipments', [
            'barcode'                      => 'S3BARCODE',
            'description'                  => 'order #012ASD',
            'price'                        => 99,
            'currency'                     => 'USD',
            'insurance_amount'             => 50,
            'physical_properties'          => [
                'weight' => 1000,
                'length' => 1100,
                'height' => 1300,
                'width'  => 1400,
                'volume' => 2002,
            ],
            'physical_properties_verified' => [
                'weight' => 100,
                'length' => 110,
                'height' => 130,
                'width'  => 140,
                'volume' => 2.002,
            ],
            'recipient_address'            => [
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment 4',
                'street_number'        => '1',
                'street_number_suffix' => 'A',
                'postal_code'          => '1AR BR2',
                'city'                 => 'London',
                'country_code'         => 'GB',
                'first_name'           => 'Robert',
                'last_name'            => 'Drop Tables',
                'company'              => 'ACME co.',
                'email'                => 'rob@tables.com',
                'phone_number'         => '+31 (0)234 567 890',
            ],
            'sender_address'               => [
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment 9',
                'street_number'        => '4',
                'street_number_suffix' => 'A',
                'postal_code'          => '1AR BR2',
                'city'                 => 'London',
                'country_code'         => 'NL',
                'first_name'           => 'Robert',
                'last_name'            => 'Drop Tables',
                'company'              => 'ACME co.',
                'email'                => 'rob@tables.com',
                'phone_number'         => '+31 (0)234 567 890',
            ],
            'pickup_location_code'         => 'CODE123',
            'pickup_location_address'      => [
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment 41',
                'street_number'        => '2',
                'street_number_suffix' => 'A',
                'postal_code'          => '1AR BR2',
                'city'                 => 'London',
                'country_code'         => 'GB',
                'first_name'           => 'Robert',
                'last_name'            => 'Drop Tables',
                'company'              => 'ACME co.',
                'email'                => 'rob@tables.com',
                'phone_number'         => '+31 (0)234 567 890',
            ],
            'items'                        => [
                [
                    'sku'                 => '123456789',
                    'description'         => 'OnePlus X',
                    'item_value'          => 100,
                    'currency'            => 'GBP',
                    'quantity'            => 2,
                    'hs_code'             => '8517.12.00',
                    'origin_country_code' => 'GB',
                ],
                [
                    'sku'                 => '213425',
                    'description'         => 'OnePlus One',
                    'item_value'          => 200,
                    'currency'            => 'GBP',
                    'quantity'            => 3,
                    'hs_code'             => '8517.12.00',
                    'origin_country_code' => 'GB',
                ],
                [
                    'sku'                 => '6876',
                    'description'         => 'OnePlus Two',
                    'item_value'          => 300,
                    'currency'            => 'GBP',
                    'quantity'            => 1,
                    'hs_code'             => '8517.12.00',
                    'origin_country_code' => 'GB',
                ],
            ],
            'customs'                      => [
                'content_type'   => 'documents',
                'invoice_number' => 'NO.5',
                'non_delivery'   => 'return',
                'incoterm'       => 'DDU',
            ],
            'register_at'                  => 1526913941,
            'shop'                         => ['id' => 'shop-id-1', 'type' => 'shops'],
            'service_contract'             => ['id' => 'service-contract-id-1', 'type' => 'service-contracts'],
            'shipment_status'              => ['id' => 'shipment-status-id-1', 'type' => 'shipment-statuses'],
            'service_options'              => [['id' => 'option-id-1', 'type' => 'service-options']],
            'files'                        => [['id' => 'file-id-1', 'type' => 'files']],
        ]);

        $this->assertInstanceOf(ShipmentInterface::class, $shipment);
        $this->assertEquals([
            'type'          => 'shipments',
            'attributes'    => [
                'barcode'                      => 'S3BARCODE',
                'description'                  => 'order #012ASD',
                'price'                        => [
                    'amount'   => 99,
                    'currency' => 'USD',
                ],
                'insurance'                    => [
                    'amount'   => 50,
                    'currency' => 'USD',
                ],
                'physical_properties'          => [
                    'weight' => 1000,
                    'length' => 1100,
                    'height' => 1300,
                    'width'  => 1400,
                    'volume' => 2002,
                ],
                'physical_properties_verified' => [
                    'weight' => 100,
                    'length' => 110,
                    'height' => 130,
                    'width'  => 140,
                    'volume' => 2.002,
                ],
                'recipient_address'            => [
                    'street_1'             => 'Diagonally',
                    'street_2'             => 'Apartment 4',
                    'street_number'        => '1',
                    'street_number_suffix' => 'A',
                    'postal_code'          => '1AR BR2',
                    'city'                 => 'London',
                    'country_code'         => 'GB',
                    'first_name'           => 'Robert',
                    'last_name'            => 'Drop Tables',
                    'company'              => 'ACME co.',
                    'email'                => 'rob@tables.com',
                    'phone_number'         => '+31 (0)234 567 890',
                ],
                'sender_address'               => [
                    'street_1'             => 'Diagonally',
                    'street_2'             => 'Apartment 9',
                    'street_number'        => '4',
                    'street_number_suffix' => 'A',
                    'postal_code'          => '1AR BR2',
                    'city'                 => 'London',
                    'country_code'         => 'NL',
                    'first_name'           => 'Robert',
                    'last_name'            => 'Drop Tables',
                    'company'              => 'ACME co.',
                    'email'                => 'rob@tables.com',
                    'phone_number'         => '+31 (0)234 567 890',
                ],
                'pickup_location'              => [
                    'code'    => 'CODE123',
                    'address' => [
                        'street_1'             => 'Diagonally',
                        'street_2'             => 'Apartment 41',
                        'street_number'        => '2',
                        'street_number_suffix' => 'A',
                        'postal_code'          => '1AR BR2',
                        'city'                 => 'London',
                        'country_code'         => 'GB',
                        'first_name'           => 'Robert',
                        'last_name'            => 'Drop Tables',
                        'company'              => 'ACME co.',
                        'email'                => 'rob@tables.com',
                        'phone_number'         => '+31 (0)234 567 890',
                    ],
                ],
                'items'                        => [
                    [
                        'sku'                 => '123456789',
                        'description'         => 'OnePlus X',
                        'item_value'          => [
                            'amount'   => 100,
                            'currency' => 'GBP',
                        ],
                        'quantity'            => 2,
                        'hs_code'             => '8517.12.00',
                        'origin_country_code' => 'GB',
                    ],
                    [
                        'sku'                 => '213425',
                        'description'         => 'OnePlus One',
                        'item_value'          => [
                            'amount'   => 200,
                            'currency' => 'GBP',
                        ],
                        'quantity'            => 3,
                        'hs_code'             => '8517.12.00',
                        'origin_country_code' => 'GB',
                    ],
                    [
                        'sku'                 => '6876',
                        'description'         => 'OnePlus Two',
                        'item_value'          => [
                            'amount'   => 300,
                            'currency' => 'GBP',
                        ],
                        'quantity'            => 1,
                        'hs_code'             => '8517.12.00',
                        'origin_country_code' => 'GB',
                    ],
                ],
                'customs'                      => [
                    'content_type'   => 'documents',
                    'invoice_number' => 'NO.5',
                    'non_delivery'   => 'return',
                    'incoterm'       => 'DDU',
                ],
                'register_at'                  => 1526913941,
            ],
            'relationships' => [
                'shop'             => ['data' => ['id' => 'shop-id-1', 'type' => 'shops']],
                'service_contract' => ['data' => ['id' => 'service-contract-id-1', 'type' => 'service-contracts']],
                'shipment_status'  => ['data' => ['id' => 'shipment-status-id-1', 'type' => 'shipment-statuses']],
                'service_options'  => ['data' => [['id' => 'option-id-1', 'type' => 'service-options']]],
                'files'            => ['data' => [['id' => 'file-id-1', 'type' => 'files']]],
            ],
        ], $shipment->jsonSerialize());
    }

    /** @test */
    public function testCreateEmptyShop()
    {
        $resourceFactory = new ResourceFactory();
        $shop = $resourceFactory->create('shops');

        $this->assertInstanceOf(ShopInterface::class, $shop);
        $this->assertEquals([
            'type' => 'shops',
        ], $shop->jsonSerialize());
    }

    /** @test */
    public function testCreateShop()
    {
        $resourceFactory = new ResourceFactory();
        $shop = $resourceFactory->create('shops', [
            'name'            => 'MyParcel.com Test Shop',
            'billing_address' => [
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment 3',
                'street_number'        => '4',
                'street_number_suffix' => 'A',
                'postal_code'          => '1AR BR2',
                'city'                 => 'London',
                'region_code'          => 'NH',
                'country_code'         => 'AF',
                'first_name'           => 'Robert',
                'last_name'            => 'Drop Tables',
                'company'              => 'ACME co.',
                'email'                => 'rob@tables.com',
                'phone_number'         => '+31 (0)234 567 890',
            ],
            'return_address'  => [
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment 1',
                'street_number'        => '4',
                'street_number_suffix' => 'A',
                'postal_code'          => '1AR BR2',
                'city'                 => 'London',
                'region_code'          => 'NH',
                'country_code'         => 'AF',
                'first_name'           => 'Robert',
                'last_name'            => 'Drop Tables',
                'company'              => 'ACME co.',
                'email'                => 'rob@tables.com',
                'phone_number'         => '+31 (0)234 567 890',
            ],
        ]);

        $this->assertInstanceOf(ShopInterface::class, $shop);
        $this->assertEquals([
            'type'       => 'shops',
            'attributes' => [
                'name'            => 'MyParcel.com Test Shop',
                'billing_address' => [
                    'street_1'             => 'Diagonally',
                    'street_2'             => 'Apartment 3',
                    'street_number'        => '4',
                    'street_number_suffix' => 'A',
                    'postal_code'          => '1AR BR2',
                    'city'                 => 'London',
                    'region_code'          => 'NH',
                    'country_code'         => 'AF',
                    'first_name'           => 'Robert',
                    'last_name'            => 'Drop Tables',
                    'company'              => 'ACME co.',
                    'email'                => 'rob@tables.com',
                    'phone_number'         => '+31 (0)234 567 890',
                ],
                'return_address'  => [
                    'street_1'             => 'Diagonally',
                    'street_2'             => 'Apartment 1',
                    'street_number'        => '4',
                    'street_number_suffix' => 'A',
                    'postal_code'          => '1AR BR2',
                    'city'                 => 'London',
                    'region_code'          => 'NH',
                    'country_code'         => 'AF',
                    'first_name'           => 'Robert',
                    'last_name'            => 'Drop Tables',
                    'company'              => 'ACME co.',
                    'email'                => 'rob@tables.com',
                    'phone_number'         => '+31 (0)234 567 890',
                ],
            ],
        ], $shop->jsonSerialize());
    }

    /** @test */
    public function testCreateEmptyFile()
    {
        $resourceFactory = new ResourceFactory();
        $file = $resourceFactory->create('files');

        $this->assertInstanceOf(FileInterface::class, $file);
        $this->assertEquals([
            'type' => 'files',
        ], $file->jsonSerialize());
    }

    /** @test */
    public function testCreateFile()
    {
        // Mock a response from the http client.
        $api = $this->getMockBuilder(MyParcelComApiInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $resourceFactory = (new ResourceFactory())
            ->setMyParcelComApi($api);

        $file = $resourceFactory->create('files', [
            'id'            => 'file-id',
            'document_type' => 'label',
            'formats'       => [
                [
                    'extension' => 'pdf',
                    'mime_type' => 'application/pdf',
                ],
                [
                    'extension' => 'png',
                    'mime_type' => 'image/png',
                ],
            ],
        ]);

        $this->assertInstanceOf(FileInterface::class, $file);
        $this->assertEquals([
            'id'         => 'file-id',
            'type'       => 'files',
            'attributes' => [
                'document_type' => 'label',
                'formats'       => [
                    [
                        'extension' => 'pdf',
                        'mime_type' => 'application/pdf',
                    ],
                    [
                        'extension' => 'png',
                        'mime_type' => 'image/png',
                    ],
                ],
            ],
        ], $file->jsonSerialize());
    }
}
