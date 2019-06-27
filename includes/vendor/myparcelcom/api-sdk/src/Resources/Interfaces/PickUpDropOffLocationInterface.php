<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

use MyParcelCom\ApiSdk\Utils\DistanceUtils;

interface PickUpDropOffLocationInterface extends ResourceInterface
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param AddressInterface $address
     * @return $this
     */
    public function setAddress(AddressInterface $address);

    /**
     * @return AddressInterface
     */
    public function getAddress();

    /**
     * @param OpeningHourInterface[] $openingHours
     * @return $this
     */
    public function setOpeningHours(array $openingHours);

    /**
     * @param OpeningHourInterface $openingHour
     * @return $this
     */
    public function addOpeningHour(OpeningHourInterface $openingHour);

    /**
     * @return OpeningHourInterface[]
     */
    public function getOpeningHours();

    /**
     * @param PositionInterface $position
     * @return $this
     */
    public function setPosition(PositionInterface $position);

    /**
     * @return PositionInterface
     */
    public function getPosition();

    /**
     * @param int    $distance
     * @param string $unit
     * @return $this
     */
    public function setDistance($distance, $unit = DistanceUtils::UNIT_METER);

    /**
     * @param string $unit
     * @return int
     */
    public function getDistance($unit = DistanceUtils::UNIT_METER);

    /**
     * @param string[] $category
     * @return $this
     */
    public function setCategories(array $category);

    /**
     * @return array
     */
    public function getCategories();
}
