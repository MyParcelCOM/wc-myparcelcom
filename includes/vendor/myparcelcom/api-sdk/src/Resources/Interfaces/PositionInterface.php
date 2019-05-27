<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

use JsonSerializable;

interface PositionInterface extends JsonSerializable
{
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
}
