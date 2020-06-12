<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

use DateTime;

interface OpeningHourInterface extends \JsonSerializable
{
    /**
     * @param string $day
     * @return $this
     */
    public function setDay($day);

    /**
     * @return string
     */
    public function getDay();

    /**
     * @param DateTime|string $open
     * @return $this
     */
    public function setOpen($open);

    /**
     * @return DateTime
     */
    public function getOpen();

    /**
     * @param DateTime|string $closed
     * @return $this
     */
    public function setClosed($closed);

    /**
     * @return DateTime
     */
    public function getClosed();
}
