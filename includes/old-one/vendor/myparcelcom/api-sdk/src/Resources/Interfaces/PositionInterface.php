<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface PositionInterface extends \JsonSerializable
{
    const UNIT_KILOMETER = 'kilometers';
    const UNIT_METER = 'meters';
    const UNIT_MILE = 'miles';
    const UNIT_FOOT = 'feet';

    /**
     * @param float $latitude
     * @return $this
     */
    public function setLatitude($latitude);

    /**
     * @return float
     */
    public function getLatitude();

    /**
     * @param float $longitude
     * @return $this
     */
    public function setLongitude($longitude);

    /**
     * @return float
     */
    public function getLongitude();

    /**
     * @param int    $distance
     * @param string $unit
     * @return $this
     */
    public function setDistance($distance, $unit = self::UNIT_METER);

    /**
     * @param string $unit
     * @return int
     */
    public function getDistance($unit = self::UNIT_METER);
}
