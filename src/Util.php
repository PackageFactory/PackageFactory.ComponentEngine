<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine;

class Util
{
    /**
     * @param mixed $value
     * @return boolean
     */
    public static function isTrueish($value): bool
    {
        if (is_string($value)) {
            return $value !== '';
        } elseif (is_numeric($value)) {
            return $value !== 0.0;
        } elseif (is_null($value)) {
            return false;
        } elseif (is_bool($value)) {
            return $value;
        } else {
            return (bool) $value;
        }
    }
}