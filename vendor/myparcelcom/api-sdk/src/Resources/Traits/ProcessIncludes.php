<?php

namespace MyParcelCom\ApiSdk\Resources\Traits;

trait ProcessIncludes
{
    public function processIncludedResources(array $includedResources)
    {
        foreach ($this::INCLUDES as $resourceType => $relationshipKey) {
            $relationship = $this->relationships[$relationshipKey]['data'];

            // Only support included resources for single relationships - TODO: add support for relationship arrays.
            if (is_array($relationship)) {
                return;
            }

            foreach ($includedResources as $resource) {
                if ($resource->getType() === $resourceType && $resource->getId() === $relationship->getId()) {
                    $this->relationships[$relationshipKey]['data'] = $resource;
                }
            }
        }
    }
}
