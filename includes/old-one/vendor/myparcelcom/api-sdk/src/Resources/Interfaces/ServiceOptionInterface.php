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
}
