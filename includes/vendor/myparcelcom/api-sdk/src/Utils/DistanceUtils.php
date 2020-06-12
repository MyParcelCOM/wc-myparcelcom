<?php

namespace MyParcelCom\ApiSdk\Utils;

use MyParcelCom\ApiSdk\Exceptions\ConversionException;

class DistanceUtils
{
    const UNIT_KILOMETER = 'kilometers';
    const UNIT_METER = 'meters';
    const UNIT_MILE = 'miles';
    const UNIT_FOOT = 'feet';

    /** @var array */
    private static $unitConversion = [
        self::UNIT_METER     => 1,
        self::UNIT_KILOMETER => 1000,
        self::UNIT_MILE      => 1609.344,
        self::UNIT_FOOT      => 0.3048,
    ];

    /**
     * Converts given distance from $sourceUnit to $destinationUnit.
     *
     * @example DistanceUtils::convertDistance(100, DistanceUtils::UNIT_KILOMETER, DistanceUtils::UNIT_MILE)
     *          will return `62,137119224`.
     *
     * @param float|int $distance
     * @param string    $sourceUnit
     * @param string    $destinationUnit
     * @return float|int
     */
    public static function convertDistance($distance, $sourceUnit, $destinationUnit)
    {
        if (!isset(self::$unitConversion[$sourceUnit])) {
            throw new ConversionException('Cannot convert distance with unit: ' . $sourceUnit);
        }
        if (!isset(self::$unitConversion[$destinationUnit])) {
            throw new ConversionException('Cannot convert to distance with unit: ' . $destinationUnit);
        }

        if ($sourceUnit === $destinationUnit) {
            return $distance;
        }

        return $distance * self::$unitConversion[$sourceUnit] / self::$unitConversion[$destinationUnit];
    }
}
