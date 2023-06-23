<?php

namespace MyParcelCom\ApiSdk\Resources;

use DateTime;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierStatusInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Utils\DateUtils;

class CarrierStatus implements CarrierStatusInterface
{
    use JsonSerializable;

    /** @var string */
    protected $code;

    /** @var string */
    protected $description;

    /** @var int */
    protected $assignedAt;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getAssignedAt()
    {
        return isset($this->assignedAt)
            ? (new DateTime())->setTimestamp($this->assignedAt)
            : null;
    }

    /**
     * @param DateTime|string|int $assignedAt
     * @return $this
     */
    public function setAssignedAt($assignedAt)
    {
        $this->assignedAt = DateUtils::toTimestamp($assignedAt);

        return $this;
    }
}
