<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

use JsonSerializable;

interface ErrorInterface extends JsonSerializable
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getId();

    /**
     * @param array $links
     * @return $this
     */
    public function setLinks(array $links);

    /**
     * @return array
     */
    public function getLinks();

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getStatus();

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
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $detail
     * @return $this
     */
    public function setDetail($detail);

    /**
     * @return string
     */
    public function getDetail();

    /**
     * @param array $source
     * @return $this
     */
    public function setSource(array $source);

    /**
     * @return array
     */
    public function getSource();

    /**
     * @param array $meta
     * @return $this
     */
    public function setMeta(array $meta);

    /**
     * @return array
     */
    public function getMeta();
}
