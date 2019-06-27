<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

use MyParcelCom\ApiSdk\MyParcelComApiInterface;

interface ResourceProxyInterface
{
    /**
     * Set the API with which to fetch the proxied resource.
     *
     * @param MyParcelComApiInterface $api
     * @return $this
     */
    public function setMyParcelComApi(MyParcelComApiInterface $api);
}
