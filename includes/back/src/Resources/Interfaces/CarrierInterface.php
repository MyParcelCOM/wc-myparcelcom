<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface CarrierInterface extends ResourceInterface
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
     * @param array $format
     * @return $this
     */
    public function setCredentialsFormat(array $format);

    /**
     * @return array
     */
    public function getCredentialsFormat();
}
