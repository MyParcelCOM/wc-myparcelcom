<?php

namespace MyParcelCom\ApiSdk\Resources\Traits;

use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;

trait ProxiesResource
{
    /** @var ResourceInterface */
    private $resource;

    /** @var MyParcelComApiInterface */
    private $api;

    /** @var string */
    private $uri;

    /**
     * Set the api to use when retrieving the resource.
     *
     * @param MyParcelComApiInterface $api
     * @return $this
     */
    public function setMyParcelComApi(MyParcelComApiInterface $api = null)
    {
        $this->api = $api;

        return $this;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setResourceUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get the resource that this instance is a proxy for.
     *
     * @return ResourceInterface
     */
    protected function getResource()
    {
        if (isset($this->resource)) {
            return $this->resource;
        }

        if (!isset($this->api)) {
            throw  new MyParcelComException('No API object set on proxy, cannot retrieve resource');
        }

        if (isset($this->uri)) {
            $resources = $this->api->getResourcesFromUri($this->uri);
            $this->resource = reset($resources);
        } else {
            $this->resource = $this->api->getResourceById($this->getType(), $this->getId());
        }

        return $this->resource;
    }
}
