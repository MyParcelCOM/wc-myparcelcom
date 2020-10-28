<?php

namespace MyParcelCom\ApiSdk\Resources\Traits;

use MyParcelCom\ApiSdk\Utils\StringUtils;

trait JsonSerializable
{
    /**
     * This function puts all object properties in an array and returns it.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $json = $this->arrayValuesToArray(get_object_vars($this));

        // We remove all empty properties
        foreach ($json as $property => $value) {
            if ($this->isEmpty($value)) {
                unset($json[$property]);
            }
        }

        // If there are attributes, we make sure no empty attributes are serialized
        if (isset($json['attributes'])) {
            foreach ($json['attributes'] as $attribute => $value) {
                if ($this->isEmpty($value)) {
                    unset($json['attributes'][$attribute]);
                }
            }
        }

        // If there are relationships, we remove any possible attributes still
        // present. This can happen when a resource (not a proxy) is set as a
        // relationship on another resource.
        if (isset($json['relationships'])) {
            $json['relationships'] = $this->removeRelationshipAttributes($json['relationships']);
        }

        return $json;
    }

    /**
     * @param mixed $values
     * @return bool
     */
    private function isEmpty($values)
    {
        if ($values === [] || $values === null) {
            return true;
        }

        if (!is_array($values)) {
            return false;
        }

        $empty = true;
        foreach ($values as $value) {
            $empty = $empty && $this->isEmpty($value);
        }

        return $empty;
    }

    /**
     * Helper function to recursively convert all values in an array to scalar
     * values or arrays with scalar values.
     *
     * @param array $arrayValues
     * @return array
     */
    private function arrayValuesToArray(array $arrayValues)
    {
        $array = [];
        foreach ($arrayValues as $key => $value) {
            $key = StringUtils::camelToSnakeCase($key);

            if (is_scalar($value)) {
                $array[$key] = $value;
            } elseif (is_array($value)) {
                $array[$key] = $this->arrayValuesToArray($value);
            } elseif (method_exists($value, 'jsonSerialize')) {
                $array[$key] = $value->jsonSerialize();
            }
        }

        return $array;
    }

    /**
     * Remove all the attributes from the relationships, so it only has `id` and
     * `type` values.
     *
     * @param array $relationships
     * @return array
     */
    private function removeRelationshipAttributes(array $relationships)
    {
        foreach ($relationships as $name => &$relationship) {
            if (empty($relationship['data'])) {
                unset($relationships[$name]);
                continue;
            }
            if (isset($relationship['data']['id'])) {
                unset($relationship['data']['attributes'], $relationship['data']['relationships']);
                continue;
            }
            foreach ($relationship['data'] as &$relationResource) {
                unset($relationResource['attributes'], $relationResource['relationships']);
            }
        }
        unset($relationship);

        return $relationships;
    }
}
