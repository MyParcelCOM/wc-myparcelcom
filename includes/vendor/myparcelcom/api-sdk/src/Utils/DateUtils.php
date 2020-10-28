<?php

namespace MyParcelCom\ApiSdk\Utils;

use DateTime;
use InvalidArgumentException;

class DateUtils
{
    /**
     * @param DateTime|string|int $dateTime
     * @return int
     */
    public static function toTimestamp($dateTime)
    {
        if (is_int($dateTime)) {
            return $dateTime;
        }

        if (is_string($dateTime)) {
            return (new DateTime($dateTime))->getTimestamp();
        }

        if ($dateTime instanceof DateTime) {
            return $dateTime->getTimestamp();
        }

        throw new InvalidArgumentException(
            sprintf(
                '$dateTime must be an instance of DateTime, string or integer, %s given',
                gettype($dateTime) === 'object'
                    ? get_class($dateTime)
                    : gettype($dateTime)
            )
        );
    }
}
