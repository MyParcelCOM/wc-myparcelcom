<?php

namespace MyParcelCom\ApiSdk\Resources;

use DateTime;
use MyParcelCom\ApiSdk\Resources\Interfaces\OpeningHourInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class OpeningHour implements OpeningHourInterface
{
    use JsonSerializable;

    /** @var string */
    private $day;

    /** @var string */
    private $open;

    /** @var DateTime */
    private $openDateTime;

    /** @var string */
    private $closed;

    /** @var DateTime */
    private $closedDateTime;

    /**
     * {@inheritdoc}
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * {@inheritdoc}
     */
    public function setOpen($open)
    {
        if (is_string($open)) {
            $this->open = $open;
            $this->openDateTime = new DateTime($open);
        } elseif ($open instanceof DateTime) {
            $this->open = $open->format('H:i');
            $this->openDateTime = $open;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOpen()
    {
        return $this->openDateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function setClosed($closed)
    {
        if (is_string($closed)) {
            $this->closed = $closed;
            $this->closedDateTime = new DateTime($closed);
        } elseif ($closed instanceof DateTime) {
            $this->closed = $closed->format('H:i');
            $this->closedDateTime = $closed;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClosed()
    {
        return $this->closedDateTime;
    }
}
