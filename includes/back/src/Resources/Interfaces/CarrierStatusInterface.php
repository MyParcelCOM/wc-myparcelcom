<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

use DateTime;
use JsonSerializable;

interface CarrierStatusInterface extends JsonSerializable
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return DateTime
     */
    public function getAssignedAt();

    /**
     * @param DateTime|string|int $assignedAt
     * @return $this
     */
    public function setAssignedAt($assignedAt);
}
