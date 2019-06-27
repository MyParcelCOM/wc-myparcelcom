<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Resources\Interfaces\PhysicalPropertiesInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class PhysicalProperties implements PhysicalPropertiesInterface
{
    use JsonSerializable;

    /** @var int */
    private $weight;

    /** @var int */
    private $length;

    /** @var int */
    private $volume;

    /** @var int */
    private $height;

    /** @var int */
    private $width;

    /** @var array */
    private static $unitConversion = [
        self::WEIGHT_GRAM     => 1,
        self::WEIGHT_KILOGRAM => 1000,
        self::WEIGHT_POUND    => 453.59237,
        self::WEIGHT_OUNCE    => 28.349523125,
        self::WEIGHT_STONE    => 6350.29318,
    ];

    /**
     * {@inheritdoc}
     */
    public function setWidth($width)
    {
        $this->width = (int)$width;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeight($height)
    {
        $this->height = (int)$height;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * {@inheritdoc}
     */
    public function setLength($length)
    {
        $this->length = (int)$length;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * {@inheritdoc}
     */
    public function setWeight($weight, $unit = self::WEIGHT_GRAM)
    {
        if (!isset(self::$unitConversion[$unit])) {
            throw new MyParcelComException('invalid unit: ' . $unit);
        }

        $this->weight = (int)round($weight * self::$unitConversion[$unit]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWeight($unit = self::WEIGHT_GRAM)
    {
        if (!isset(self::$unitConversion[$unit])) {
            throw new MyParcelComException('invalid unit: ' . $unit);
        }

        return (int)round($this->weight / self::$unitConversion[$unit]);
    }

    /**
     * {@inheritdoc}
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVolume()
    {
        return $this->volume;
    }
}
