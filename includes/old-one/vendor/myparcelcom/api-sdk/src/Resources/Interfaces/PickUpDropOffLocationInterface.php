<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

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
}
