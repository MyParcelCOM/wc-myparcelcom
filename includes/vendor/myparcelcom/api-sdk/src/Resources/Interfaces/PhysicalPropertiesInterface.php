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
     * @param int $width
     * @return $this
     */
    public function setWidth($width);

    /**
     * @return int
     */
    public function getWidth();

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight($height);

    /**
     * @return int
     */
    public function getHeight();

    /**
     * @param int $length
     * @return $this
     */
    public function setLength($length);

    /**
     * @return int
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
}
