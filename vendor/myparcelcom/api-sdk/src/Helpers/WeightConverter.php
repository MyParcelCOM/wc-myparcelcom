<?php

namespace MyParcelCom\ApiSdk\Helpers;

use MyParcelCom\ApiSdk\Enums\WeightUnitEnum;
use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;

class WeightConverter
{
    /**
     * @param float|int $weight
     * @param string    $from
     * @param string    $to
     * @return int|float
     */
    public static function convert($weight, $from, $to)
    {
        $weightInGrams = self::convertToGrams($weight, $from);

        return self::convertFromGrams($weightInGrams, $to);
    }

    /**
     * @param float|int $weight
     * @param string    $from
     * @return int|float
     */
    private static function convertToGrams($weight, $from)
    {
        switch ($from) {
            case WeightUnitEnum::MILLIGRAM:
                return $weight / 1000;
            case WeightUnitEnum::GRAM:
                return $weight;
            case WeightUnitEnum::KILOGRAM:
                return $weight * 1000;
            case WeightUnitEnum::OUNCE:
                return $weight * 28.3495;
            case WeightUnitEnum::POUND:
                return $weight * 453.592;
            default:
                throw new MyParcelComException('Invalid weight unit');
        }
    }

    /**
     * @param float|int $weightInGrams
     * @param string    $to
     * @return float|int
     */
    private static function convertFromGrams($weightInGrams, $to)
    {
        switch ($to) {
            case WeightUnitEnum::MILLIGRAM:
                return $weightInGrams * 1000;
            case WeightUnitEnum::GRAM:
                return $weightInGrams;
            case WeightUnitEnum::KILOGRAM:
                return $weightInGrams / 1000;
            case WeightUnitEnum::OUNCE:
                return $weightInGrams / 28.3495;
            case WeightUnitEnum::POUND:
                return $weightInGrams / 453.592;
            default:
                throw new MyParcelComException('Invalid weight unit');
        }
    }
}
