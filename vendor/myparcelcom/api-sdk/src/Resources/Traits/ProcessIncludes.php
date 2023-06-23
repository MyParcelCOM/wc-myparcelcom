<?php

namespace MyParcelCom\ApiSdk\Resources\Traits;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;

trait ProcessIncludes
{
    /**
     * Replace relationships ResourceProxys (id + type) with full Resources built from `included` to avoid lazy loading.
     * It will only process included data for which $this::INCLUDES defines which include belongs to which relationship.
     *
     * @param ResourceInterface[] $includedResources
     */
    public function processIncludedResources(array $includedResources)
    {
        foreach ($includedResources as $resource) {
            if (!array_key_exists($resource->getType(), self::INCLUDES)) {
                return;
            }

            $relationshipKey = $this::INCLUDES[$resource->getType()];
            $relationship = $this->relationships[$relationshipKey]['data'];

            /** @var ResourceInterface|ResourceInterface[] $relationship */
            if (is_array($relationship)) {
                foreach ($relationship as $index => $item) {
                    if ($resource->getType() === $item->getType() && $resource->getId() === $item->getId()) {
                        $this->relationships[$relationshipKey]['data'][$index] = $resource;
                        break;
                    }
                }
            } else {
                if ($resource->getType() === $relationship->getType() && $resource->getId() === $relationship->getId()) {
                    $this->relationships[$relationshipKey]['data'] = $resource;
                }
            }
        }
    }
}
