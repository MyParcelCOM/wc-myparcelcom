<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface AddressInterface extends \JsonSerializable
{
    /**
     * @return string|null
     */
    public function getStreet1();

    /**
     * @param string $street1
     * @return $this
     */
    public function setStreet1($street1);

    /**
     * @return string|null
     */
    public function getStreet2();

    /**
     * @param string $street2
     * @return $this
     */
    public function setStreet2($street2);

    /**
     * @return int|null
     */
    public function getStreetNumber();

    /**
     * @param int $streetNumber
     * @return $this
     */
    public function setStreetNumber($streetNumber);

    /**
     * @return string|null
     */
    public function getStreetNumberSuffix();

    /**
     * @param string $streetNumberSuffix
     * @return $this
     */
    public function setStreetNumberSuffix($streetNumberSuffix);

    /**
     * @return string|null
     */
    public function getPostalCode();

    /**
     * @param string $postalCode
     * @return $this
     */
    public function setPostalCode($postalCode);

    /**
     * @return string|null
     */
    public function getCity();

    /**
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * @return string|null
     */
    public function getRegionCode();

    /**
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode);

    /**
     * @return string|null
     */
    public function getCountryCode();

    /**
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode);

    /**
     * @return string|null
     */
    public function getFirstName();

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName);

    /**
     * @return string|null
     */
    public function getLastName();

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName);

    /**
     * @return string|null
     */
    public function getCompany();

    /**
     * @param string $company
     * @return $this
     */
    public function setCompany($company);

    /**
     * @return string|null
     */
    public function getEmail();

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * @return string|null
     */
    public function getPhoneNumber();

    /**
     * @param string $phoneNumber
     * @return $this
     */
    public function setPhoneNumber($phoneNumber);
}
