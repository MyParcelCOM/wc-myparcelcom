<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Resources\Interfaces\PositionInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class Position implements PositionInterface
{
    use JsonSerializable;

    /** @var float */
    private $latitude;

    /** @var float */
    private $longitude;

    /** @var int */
    private $distance;

    /** @var array */
    private static $unitConversion = [
        self::UNIT_METER     => 1,
        self::UNIT_KILOMETER => 1000,
        self::UNIT_MILE      => 1609.344,
        self::UNIT_FOOT      => 0.3048,
    ];

    /**
     * {@inheritdoc}
     */
    public function setLatitude($latitude)
    {
        $this->latitude = (float)$latitude;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * {@inheritdoc}
     */
    public function setLongitude($longitude)
    {
        $this->longitude = (float)$longitude;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * {@inheritdoc}
     */
    public function setDistance($distance, $unit = self::UNIT_METER)
    {
        if (!isset(self::$unitConversion[$unit])) {
            throw new MyParcelComException('invalid unit: ' . $unit);
        }

        $this->distance = round($distance * self::$unitConversion[$unit]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDistance($unit = self::UNIT_METER)
    {
        if (!isset(self::$unitConversion[$unit])) {
            throw new MyParcelComException('invalid unit: ' . $unit);
        }

        return round($this->distance / self::$unitConversion[$unit]);
    }
}
