<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Resources\Interfaces\AddressInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CustomsInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\PhysicalPropertiesInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentItemInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentStatusInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;
use MyParcelCom\ApiSdk\Resources\Shipment;
use PHPUnit\Framework\TestCase;

class ShipmentTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $shipment = new Shipment();
        $this->assertEquals('shipment-id', $shipment->setId('shipment-id')->getId());
    }

    /** @test */
    public function testType()
    {
        $shipment = new Shipment();
        $this->assertEquals('shipments', $shipment->getType());
    }

    /** @test */
    public function testRecipientAddress()
    {
        $shipment = new Shipment();

        $mock = $this->getMockClass(AddressInterface::class);
        $address = new $mock();

        $this->assertEquals($address, $shipment->setRecipientAddress($address)->getRecipientAddress());
    }

    /** @test */
    public function testSenderAddress()
    {
        $shipment = new Shipment();

        $mock = $this->getMockClass(AddressInterface::class);
        $address = new $mock();

        $this->assertEquals($address, $shipment->setSenderAddress($address)->getSenderAddress());
    }

    /** @test */
    public function testReturnAddress()
    {
        $shipment = new Shipment();

        $mock = $this->getMockClass(AddressInterface::class);
        $address = new $mock();

        $this->assertEquals($address, $shipment->setReturnAddress($address)->getReturnAddress());
    }

    /** @test */
    public function testPickupLocationCode()
    {
        $shipment = new Shipment();
        $this->assertEquals('CODE123', $shipment->setPickupLocationCode('CODE123')->getPickupLocationCode());
    }

    /** @test */
    public function testPickupLocationAddress()
    {
        $shipment = new Shipment();

        $mock = $this->getMockClass(AddressInterface::class);
        $address = new $mock();

        $this->assertEquals($address, $shipment->setPickupLocationAddress($address)->getPickupLocationAddress());
    }

    /** @test */
    public function testDescription()
    {
        $shipment = new Shipment();
        $this->assertEquals('order #012ASD', $shipment->setDescription('order #012ASD')->getDescription());
    }

    /** @test */
    public function testPrice()
    {
        $shipment = new Shipment();
        $this->assertEquals(99, $shipment->setPrice(99)->getPrice());
    }

    /** @test */
    public function testCurrency()
    {
        $shipment = new Shipment();
        $this->assertEquals('USD', $shipment->setCurrency('USD')->getCurrency());
    }

    /** @test */
    public function testBarcode()
    {
        $shipment = new Shipment();
        $this->assertEquals('S3BARCODE', $shipment->setBarcode('S3BARCODE')->getBarcode());
    }

    /** @test */
    public function testTrackingCode()
    {
        $shipment = new Shipment();
        $this->assertEquals('ATRACKINGCODE', $shipment->setTrackingCode('ATRACKINGCODE')->getTrackingCode());
    }

    /** @test */
    public function testTrackingUrl()
    {
        $shipment = new Shipment();
        $this->assertEquals('https://track/ATRACKINGCODE', $shipment->setTrackingUrl('https://track/ATRACKINGCODE')->getTrackingUrl());
    }

    /** @test */
    public function testWeight()
    {
        $shipment = new Shipment();
        $this->assertEquals(8000, $shipment->setWeight(8000)->getWeight());
        $this->assertEquals(500, $shipment->setWeight(500, PhysicalPropertiesInterface::WEIGHT_GRAM)->getWeight());
        $this->assertEquals(3000, $shipment->setWeight(3, PhysicalPropertiesInterface::WEIGHT_KILOGRAM)->getWeight());
        $this->assertEquals(1701, $shipment->setWeight(60, PhysicalPropertiesInterface::WEIGHT_OUNCE)->getWeight());
        $this->assertEquals(2268, $shipment->setWeight(5, PhysicalPropertiesInterface::WEIGHT_POUND)->getWeight());
        $this->assertEquals(12701, $shipment->setWeight(2, PhysicalPropertiesInterface::WEIGHT_STONE)->getWeight());
        $this->assertEquals(500, $shipment->setWeight(500)->getWeight(PhysicalPropertiesInterface::WEIGHT_GRAM));
        $this->assertEquals(3, $shipment->setWeight(3000)->getWeight(PhysicalPropertiesInterface::WEIGHT_KILOGRAM));
        $this->assertEquals(60, $shipment->setWeight(1701)->getWeight(PhysicalPropertiesInterface::WEIGHT_OUNCE));
        $this->assertEquals(5, $shipment->setWeight(2268)->getWeight(PhysicalPropertiesInterface::WEIGHT_POUND));
        $this->assertEquals(2, $shipment->setWeight(12701)->getWeight(PhysicalPropertiesInterface::WEIGHT_STONE));
    }

    /** @test */
    public function testSetWeightInvalidUnit()
    {
        $shipment = new Shipment();

        $this->expectException(MyParcelComException::class);
        $shipment->setWeight(8000, 'tons');
    }

    /** @test */
    public function testGetWeightInvalidUnit()
    {
        $shipment = new Shipment();
        $shipment->setWeight(8000);

        $this->expectException(MyParcelComException::class);
        $shipment->getWeight('truckloads');
    }

    /** @test */
    public function testPhysicalProperties()
    {
        $shipment = new Shipment();

        $mock = $this->getMockClass(PhysicalPropertiesInterface::class);
        $physicalProperties = new $mock();

        $this->assertEquals($physicalProperties, $shipment->setPhysicalProperties($physicalProperties)->getPhysicalProperties());
    }

    /** @test */
    public function testPhysicalPropertiesVerified()
    {
        $shipment = new Shipment();

        $mock = $this->getMockClass(PhysicalPropertiesInterface::class);
        $physicalProperties = new $mock();

        $this->assertEquals($physicalProperties, $shipment->setPhysicalPropertiesVerified($physicalProperties)->getPhysicalPropertiesVerified());
    }

    /** @test */
    public function testOptions()
    {
        $shipment = new Shipment();

        $this->assertEmpty($shipment->getServiceOptions());

        $mock = $this->getMockClass(ServiceOptionInterface::class);

        $options = [
            new $mock(),
            new $mock(),
        ];
        $shipment->setServiceOptions($options);
        $this->assertCount(2, $shipment->getServiceOptions());
        $this->assertEquals($options, $shipment->getServiceOptions());

        $option = new $mock();
        $shipment->addServiceOption($option);
        $options[] = $option;
        $this->assertCount(3, $shipment->getServiceOptions());
        $this->assertEquals($options, $shipment->getServiceOptions());
    }

    /** @test */
    public function testItSetsAndGetsAService()
    {
        $shipment = new Shipment();

        $mock = $this->createMock(ServiceInterface::class);

        $this->assertEquals($mock, $shipment->setService($mock)->getService());
    }

    /** @test */
    public function testItSetsAndGetsAContract()
    {
        $shipment = new Shipment();

        $mock = $this->createMock(ContractInterface::class);

        $this->assertEquals($mock, $shipment->setContract($mock)->getContract());
    }

    /** @test */
    public function testStatus()
    {
        $shipment = new Shipment();

        $this->assertEmpty($shipment->getShipmentStatus());

        $mock = $this->getMockClass(ShipmentStatusInterface::class);
        $status = new $mock();

        $this->assertEquals($status, $shipment->setShipmentStatus($status)->getShipmentStatus());
    }

    /** @test */
    public function testShop()
    {
        $shipment = new Shipment();

        $this->assertEmpty($shipment->getShop());

        $mock = $this->getMockClass(ShopInterface::class);
        $shop = new $mock();

        $this->assertEquals($shop, $shipment->setShop($shop)->getShop());
    }

    /** @test */
    public function testCustoms()
    {
        $shipment = new Shipment();

        $mock = $this->getMockClass(CustomsInterface::class);
        $customs = new $mock();

        $this->assertEquals($customs, $shipment->setCustoms($customs)->getCustoms());
    }

    /** @test */
    public function testItems()
    {
        $shipment = new Shipment();

        $this->assertEmpty($shipment->getItems());

        $mock = $this->getMockClass(ShipmentItemInterface::class);
        $items = [new $mock(), new $mock()];

        $shipment->setItems($items);
        $this->assertCount(2, $shipment->getItems());
        $this->assertEquals($items, $shipment->getItems());

        $item = new $mock();
        $items[] = $item;
        $shipment->addItem($item);
        $this->assertCount(3, $shipment->getItems());
        $this->assertEquals($items, $shipment->getItems());
    }

    /** @test */
    public function testFiles()
    {
        $shipment = new Shipment();

        $this->assertEmpty($shipment->getFiles());

        $mock = $this->getMockClass(FileInterface::class);

        $files = [
            new $mock(),
            new $mock(),
        ];
        $shipment->setFiles($files);
        $this->assertCount(2, $shipment->getFiles());
        $this->assertEquals($files, $shipment->getFiles());

        $file = new $mock();
        $shipment->addFile($file);
        $files[] = $file;
        $this->assertCount(3, $shipment->getFiles());
        $this->assertEquals($files, $shipment->getFiles());
    }

    /** @test */
    public function testGetFilesByType()
    {
        $shipment = new Shipment();

        $label = $this->getMockBuilder(FileInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $label->method('getDocumentType')
            ->willReturn(FileInterface::DOCUMENT_TYPE_LABEL);

        $printcode = $this->getMockBuilder(FileInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $printcode->method('getDocumentType')
            ->willReturn(FileInterface::DOCUMENT_TYPE_PRINTCODE);

        $shipment->setFiles([$label, $printcode]);

        $this->assertCount(1, $shipment->getFiles(FileInterface::DOCUMENT_TYPE_PRINTCODE));
        $files = $shipment->getFiles(FileInterface::DOCUMENT_TYPE_PRINTCODE);
        $this->assertEquals($printcode, reset($files));

        $this->assertCount(1, $shipment->getFiles(FileInterface::DOCUMENT_TYPE_LABEL));
        $files = $shipment->getFiles(FileInterface::DOCUMENT_TYPE_LABEL);
        $this->assertEquals($label, reset($files));
    }

    /** @test */
    public function testStatusHistory()
    {
        $mock = $this->getMockClass(ShipmentStatusInterface::class);
        $statuses = [
            new $mock(),
            new $mock(),
            new $mock(),
        ];

        $shipment = new Shipment();
        $shipment->setStatusHistoryCallback(function () use ($statuses) {
            return $statuses;
        });

        $this->assertEquals($statuses, $shipment->getStatusHistory());

        $statuses = [
            new $mock(),
            new $mock(),
        ];

        $this->assertEquals($statuses, $shipment->setStatusHistory($statuses)->getStatusHistory());
    }

    /** @test */
    public function testRegisterAt()
    {
        $shipment = new Shipment();

        $this->assertEquals(
            1337,
            $shipment->setRegisterAt(1337)->getRegisterAt()->getTimestamp()
        );
        $this->assertEquals(
            (new \DateTime('2019-11-04'))->getTimestamp(),
            $shipment->setRegisterAt('2019-11-04')->getRegisterAt()->getTimestamp()
        );
        $now = time();
        $this->assertEquals(
            $now,
            $shipment->setRegisterAt((new \DateTime())->setTimestamp($now))->getRegisterAt()->getTimestamp()
        );

        $this->expectException(\InvalidArgumentException::class);
        $shipment->setRegisterAt(new \stdClass());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $recipientAddress = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $recipientAddress->method('jsonSerialize')
            ->willReturn([
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
            ]);

        $senderAddress = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $senderAddress->method('jsonSerialize')
            ->willReturn([
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment 4',
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
            ]);

        $returnAddress = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $returnAddress->method('jsonSerialize')
            ->willReturn([
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment 4',
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
            ]);

        $pudoAddress = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $pudoAddress->method('jsonSerialize')
            ->willReturn([
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment 4',
                'street_number'        => '3',
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
            ]);

        $file = $this->getMockBuilder(FileInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $file->method('jsonSerialize')
            ->willReturn([
                'id'   => 'file-id-1',
                'type' => 'files',
            ]);

        $option = $this->getMockBuilder(ServiceOptionInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $option->method('jsonSerialize')
            ->willReturn([
                'id'   => 'option-id-1',
                'type' => 'service-options',
            ]);

        $service = $this->getMockBuilder(ServiceInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $service->method('jsonSerialize')
            ->willReturn([
                'id'   => 'service-id-1',
                'type' => 'services',
            ]);

        $contract = $this->getMockBuilder(ContractInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $contract->method('jsonSerialize')
            ->willReturn([
                'id'   => 'contract-id-1',
                'type' => 'contracts',
            ]);

        $shop = $this->getMockBuilder(ShopInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $shop->method('jsonSerialize')
            ->willReturn([
                'id'   => 'shop-id-1',
                'type' => 'shops',
            ]);

        $physicalProperties = $this->getMockBuilder(PhysicalPropertiesInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $physicalProperties->method('jsonSerialize')
            ->willReturn([
                'weight' => 1000,
                'length' => 1100,
                'volume' => 1200,
                'height' => 1300,
                'width'  => 1400,
            ]);

        $physicalPropertiesVerified = $this->getMockBuilder(PhysicalPropertiesInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $physicalPropertiesVerified->method('jsonSerialize')
            ->willReturn([
                'weight' => 2000,
                'length' => 2100,
                'volume' => 2200,
                'height' => 2300,
                'width'  => 2400,
            ]);

        $status = $this->getMockBuilder(ShipmentStatusInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $status->method('jsonSerialize')
            ->willReturn([
                'id'   => 'shipment-status-id-1',
                'type' => 'shipment-statuses',
            ]);

        $customs = $this->getMockBuilder(CustomsInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $customs->method('jsonSerialize')
            ->willReturn([
                'content_type'   => 'documents',
                'invoice_number' => 'NO.5',
                'non_delivery'   => 'return',
                'incoterm'       => 'DDU',
            ]);

        $item = $this->getMockBuilder(ShipmentItemInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $item->method('jsonSerialize')
            ->willReturn([
                'sku'                 => '123456789',
                'description'         => 'OnePlus X',
                'item_value'          => [
                    'amount'   => 100,
                    'currency' => 'EUR',
                ],
                'quantity'            => 2,
                'hs_code'             => '8517.12.00',
                'origin_country_code' => 'GB',
            ]);

        $shipment = (new Shipment())
            ->setId('shipment-id')
            ->setDescription('order #012ASD')
            ->setPickupLocationCode('CODE123')
            ->setPrice(99)
            ->setCurrency('USD')
            ->setBarcode('S3BARCODE')
            ->setTrackingCode('ATRACKINGCODE')
            ->setTrackingUrl('https://tra.ck/ATRACKINGCODE')
            ->setPhysicalProperties($physicalProperties)
            ->setPhysicalPropertiesVerified($physicalPropertiesVerified)
            ->setShop($shop)
            ->setServiceOptions([$option])
            ->setFiles([$file])
            ->setService($service)
            ->setContract($contract)
            ->setShipmentStatus($status)
            ->setRecipientAddress($recipientAddress)
            ->setSenderAddress($senderAddress)
            ->setReturnAddress($returnAddress)
            ->setPickupLocationAddress($pudoAddress)
            ->setCustoms($customs)
            ->setItems([$item])
            ->setRegisterAt(9001);

        $this->assertEquals([
            'id'            => 'shipment-id',
            'type'          => 'shipments',
            'attributes'    => [
                'barcode'                      => 'S3BARCODE',
                'tracking_code'                => 'ATRACKINGCODE',
                'tracking_url'                 => 'https://tra.ck/ATRACKINGCODE',
                'description'                  => 'order #012ASD',
                'price'                        => [
                    'amount'   => 99,
                    'currency' => 'USD',
                ],
                'physical_properties'          => [
                    'weight' => 1000,
                    'length' => 1100,
                    'volume' => 1200,
                    'height' => 1300,
                    'width'  => 1400,
                ],
                'physical_properties_verified' => [
                    'weight' => 2000,
                    'length' => 2100,
                    'volume' => 2200,
                    'height' => 2300,
                    'width'  => 2400,
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
                    'street_2'             => 'Apartment 4',
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
                'return_address'               => [
                    'street_1'             => 'Diagonally',
                    'street_2'             => 'Apartment 4',
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
                'pickup_location'              => [
                    'code'    => 'CODE123',
                    'address' => [
                        'street_1'             => 'Diagonally',
                        'street_2'             => 'Apartment 4',
                        'street_number'        => '3',
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
                'items'                        => [
                    [
                        'sku'                 => '123456789',
                        'description'         => 'OnePlus X',
                        'item_value'          => [
                            'amount'   => 100,
                            'currency' => 'EUR',
                        ],
                        'quantity'            => 2,
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
                'register_at'                  => 9001,
            ],
            'relationships' => [
                'shop'            => ['data' => ['id' => 'shop-id-1', 'type' => 'shops']],
                'service'         => ['data' => ['id' => 'service-id-1', 'type' => 'services']],
                'contract'        => ['data' => ['id' => 'contract-id-1', 'type' => 'contracts']],
                'shipment_status' => ['data' => ['id' => 'shipment-status-id-1', 'type' => 'shipment-statuses']],
                'service_options' => ['data' => [['id' => 'option-id-1', 'type' => 'service-options']]],
                'files'           => ['data' => [['id' => 'file-id-1', 'type' => 'files']]],
            ],
        ], $shipment->jsonSerialize());
    }
}
