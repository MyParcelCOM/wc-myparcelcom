<?php

namespace MyParcelCom\ApiSdk\Utils;

class StringUtils
{
    /**
     * Transforms a string in snake_case to camelCase.
     *
     * @param string $snakeCase
     * @return string
     */
    public static function snakeToCamelCase($snakeCase)
    {
        return lcfirst(self::snakeToPascalCase($snakeCase));
    }

    /**
     * Transforms a string from snake_case to PascalCase.
     *
     * @param string $snakeCase
     * @return string
     */
    public static function snakeToPascalCase($snakeCase)
    {
        return str_replace('_', '', ucwords($snakeCase, '_'));
    }

    /**
     * Transforms a string from camelCase to snake_case.
     *
     * @param string $camelCase
     * @return string
     */
    public static function camelToSnakeCase($camelCase)
    {
        return strtolower(preg_replace('/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)|(?<=[a-z])(?=[A-Z])/', '_', $camelCase));
    }
}
