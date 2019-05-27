<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface ServiceOptionInterface extends ResourceInterface
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

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
     * @param null|string $category
     * @return $this
     */
    public function setCategory($category);

    /**
     * @return null|string
     */
    public function getCategory();

    /**
     * @param null|int $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return null|int
     */
    public function getPrice();

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency);

    /**
     * @return null|string
     */
    public function getCurrency();

    /**
     * @param null|bool $included
     * @return $this
     */
    public function setIncluded($included);

    /**
     * @return null|bool
     */
    public function isIncluded();
}
