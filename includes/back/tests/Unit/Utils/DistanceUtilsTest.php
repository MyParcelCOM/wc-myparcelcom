<?php

namespace MyParcelCom\ApiSdk\Tests\Unit\Utils;

use MyParcelCom\ApiSdk\Exceptions\ConversionException;
use MyParcelCom\ApiSdk\Utils\DistanceUtils;
use PHPUnit\Framework\TestCase;

class DistanceUtilsTest extends TestCase
{
    /** @test */
    public function testDistanceConversion()
    {
        $convertedDistance = DistanceUtils::convertDistance(100, 'kilometers', 'miles');
        $revertedDistance = DistanceUtils::convertDistance($convertedDistance, 'miles', 'kilometers');

        $this->assertEquals(62.1371192237, $convertedDistance);
        $this->assertEquals(100, $revertedDistance);
    }

    /** @test */
    public function testConversionExceptionSourceUnit()
    {
        $this->expectException(ConversionException::class);
        DistanceUtils::convertDistance(1, 'hand', 'feet');
    }

    /** @test */
    public function testConversionExceptionDestinationUnit()
    {
        $this->expectException(ConversionException::class);
        DistanceUtils::convertDistance(1, 'feet', 'hand');
    }
}
