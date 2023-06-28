<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface PhysicalPropertiesInterface extends \JsonSerializable
{
    const WEIGHT_GRAM = 'grams';
    const WEIGHT_KILOGRAM = 'kilograms';
    const WEIGHT_POUND = 'pounds';
    const WEIGHT_OUNCE = 'ounces';
    const WEIGHT_STONE = 'stones';

    /**
     * @param null|int $width
     * @return $this
     */
    public function setWidth($width);

    /**
     * @return null|int
     */
    public function getWidth();

    /**
     * @param null|int $height
     * @return $this
     */
    public function setHeight($height);

    /**
     * @return null|int
     */
    public function getHeight();

    /**
     * @param null|int $length
     * @return $this
     */
    public function setLength($length);

    /**
     * @return null|int
     */
    public function getLength();

    /**
     * @param int    $weight
     * @param string $unit
     * @return $this
     */
    public function setWeight($weight, $unit = self::WEIGHT_GRAM);

    /**
     * @param string $unit
     * @return int
     */
    public function getWeight($unit = self::WEIGHT_GRAM);

    /**
     * @param int $volume
     * @return $this
     */
    public function setVolume($volume);

    /**
     * @return int
     */
    public function getVolume();

    /**
     * @param int $volumetricWeight
     * @return $this
     */
    public function setVolumetricWeight($volumetricWeight);

    /**
     * Returns the volumetric weight if already set.
     * Calculates the shipment's volumetric weight in grams based on set dimensions otherwise.
     *
     * @return null|int Returns null if not all dimensions are set.
     */
    public function getVolumetricWeight();
}
