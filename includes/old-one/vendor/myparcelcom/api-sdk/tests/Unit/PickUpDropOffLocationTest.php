<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\Carrier;
use MyParcelCom\ApiSdk\Resources\Interfaces\AddressInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\OpeningHourInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\PositionInterface;
use MyParcelCom\ApiSdk\Resources\PickUpDropOffLocation;
use PHPUnit\Framework\TestCase;

class PickUpDropOffLocationTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $pudoLocation = new PickUpDropOffLocation();
        $this->assertEquals('pudo-id', $pudoLocation->setId('pudo-id')->getId());
    }

    /** @test */
    public function testGetType()
    {
        $pudoLocation = new PickUpDropOffLocation();
        $this->assertEquals('pickup-dropoff-locations', $pudoLocation->getType());
    }

    /** @test */
    public function testCode()
    {
        $pudoLocation = new PickUpDropOffLocation();
        $this->assertEquals('A123', $pudoLocation->setCode('A123')->getCode());
    }

    /** @test */
    public function testAddress()
    {
        $mock = $this->getMockClass(AddressInterface::class);
        $address = new $mock();

        $pudoLocation = new PickUpDropOffLocation();
        $this->assertEquals($address, $pudoLocation->setAddress($address)->getAddress());
    }

    /** @test */
    public function testOpeningHours()
    {
        $pudoLocation = new PickUpDropOffLocation();

        $this->assertEmpty($pudoLocation->getOpeningHours());

        $mock = $this->getMockClass(OpeningHourInterface::class);
        $openingHours = [new $mock(), new $mock()];

        $pudoLocation->setOpeningHours($openingHours);
        $this->assertCount(2, $pudoLocation->getOpeningHours());
        $this->assertEquals($openingHours, $pudoLocation->getOpeningHours());

        $openingHour = new $mock();
        $openingHours[] = $openingHour;
        $pudoLocation->addOpeningHour($openingHour);
        $this->assertCount(3, $pudoLocation->getOpeningHours());
        $this->assertEquals($openingHours, $pudoLocation->getOpeningHours());
    }

    /** @test */
    public function testPosition()
    {
        $pudoLocation = new PickUpDropOffLocation();

        $mock = $this->getMockClass(PositionInterface::class);
        $position = new $mock();

        $this->assertEquals($position, $pudoLocation->setPosition($position)->getPosition());
    }

    /** @test */
    public function testCarrier()
    {
        $pudoLocation = new PickUpDropOffLocation();

        $mock = $this->getMockClass(CarrierInterface::class);
        $carrier = new $mock();

        $this->assertEquals($carrier, $pudoLocation->setCarrier($carrier)->getCarrier());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $address = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $address->method('jsonSerialize')
            ->willReturn([
                'street_1'             => 'Diagonally',
                'street_2'             => 'Apartment 4',
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
            ]);

        $openingHour = $this->getMockBuilder(OpeningHourInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $openingHour->method('jsonSerialize')
            ->willReturn([
                'day'    => 'Sunday',
                'open'   => '09:00',
                'closed' => '19:00',
            ]);

        $position = $this->getMockBuilder(PositionInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $position->method('jsonSerialize')
            ->willReturn([
                'latitude'  => 1.2345,
                'longitude' => 2.34567,
                'distance'  => 5000,
            ]);

        $pudoLocation = (new PickUpDropOffLocation())
            ->setId('pudo-id')
            ->setAddress($address)
            ->setOpeningHours([$openingHour])
            ->setPosition($position);

        $this->assertEquals([
            'id'         => 'pudo-id',
            'type'       => 'pickup-dropoff-locations',
            'attributes' => [
                'address'       => [
                    'street_1'             => 'Diagonally',
                    'street_2'             => 'Apartment 4',
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
                'opening_hours' => [
                    [
                        'day'    => 'Sunday',
                        'open'   => '09:00',
                        'closed' => '19:00',
                    ],
                ],
                'position'      => [
                    'latitude'  => 1.2345,
                    'longitude' => 2.34567,
                    'distance'  => 5000,
                ],
            ],
        ], $pudoLocation->jsonSerialize());
    }
}
