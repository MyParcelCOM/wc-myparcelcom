<?php

namespace MyParcelCom\ApiSdk\Enums;

use MyCLabs\Enum\Enum;

/**
 * @method static WeightUnitEnum MILLIGRAM()
 * @method static WeightUnitEnum GRAM()
 * @method static WeightUnitEnum KILOGRAM()
 * @method static WeightUnitEnum OUNCE()
 * @method static WeightUnitEnum POUND()
 */
class WeightUnitEnum extends Enum
{
    const MILLIGRAM = 'mg';
    const GRAM = 'g';
    const KILOGRAM = 'kg';
    const OUNCE = 'oz';
    const POUND = 'lb';
}
