<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Resources\Position;
use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    /** @test */
    public function testLatitude()
    {
        $position = new Position();
        $this->assertEquals(2.56784948, $position->setLatitude(2.56784948)->getLatitude());
    }

    /** @test */
    public function testLongitude()
    {
        $position = new Position();
        $this->assertEquals(1.123465498, $position->setLongitude(1.123465498)->getLongitude());
    }

    /** @test */
    public function testDistance()
    {
        $position = new Position();
        $this->assertEquals(900, $position->setDistance(900)->getDistance());
        $this->assertEquals(80, $position->setDistance(80, Position::UNIT_METER)->getDistance());
        $this->assertEquals(3000, $position->setDistance(3, Position::UNIT_KILOMETER)->getDistance());
        $this->assertEquals(1524, $position->setDistance(5000, Position::UNIT_FOOT)->getDistance());
        $this->assertEquals(19312, $position->setDistance(12, Position::UNIT_MILE)->getDistance());
        $this->assertEquals(80, $position->setDistance(80)->getDistance(Position::UNIT_METER));
        $this->assertEquals(3, $position->setDistance(3000)->getDistance(Position::UNIT_KILOMETER));
        $this->assertEquals(5000, $position->setDistance(1524)->getDistance(Position::UNIT_FOOT));
        $this->assertEquals(12, $position->setDistance(19312)->getDistance(Position::UNIT_MILE));
    }

    /** @test */
    public function testSetDistanceInvalidUnit()
    {
        $position = new Position();

        $this->expectException(MyParcelComException::class);
        $position->setDistance(900, 'lightyears');
    }

    /** @test */
    public function testGetDistanceInvalidUnit()
    {
        $position = new Position();
        $position->setDistance(900);

        $this->expectException(MyParcelComException::class);
        $position->getDistance('au');
    }

    /** @test */
    public function testJsonSerialize()
    {
        $position = (new Position())
            ->setLatitude(2.56784948)
            ->setLongitude(1.123465498)
            ->setDistance(900);

        $this->assertEquals([
            'latitude'  => 2.56784948,
            'longitude' => 1.123465498,
            'distance'  => 900,
        ], $position->jsonSerialize());
    }
}
