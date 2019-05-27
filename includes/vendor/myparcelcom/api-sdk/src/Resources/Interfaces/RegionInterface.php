<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface RegionInterface extends ResourceInterface
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode);

    /**
     * @return string
     */
    public function getCountryCode();

    /**
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode);

    /**
     * @return string
     */
    public function getRegionCode();

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency);

    /**
     * @return string
     */
    public function getCurrency();

    /**
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $category
     * @return $this
     */
    public function setCategory($category);

    /**
     * @return string
     */
    public function getCategory();

    /**
     * @param RegionInterface $region
     * @return $this
     */
    public function setParent(RegionInterface $region);

    /**
     * @return RegionInterface|null
     */
    public function getParent();
}
