<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value;

/**
 * @template V
 * @extends Value<V>
 */
abstract class PhpValue extends Value
{
    /**
     * @param mixed $value
     * @return Value<mixed>
     */
    final public static function fromAny($value): Value
    {
        if (is_string($value)) {
            return StringValue::fromString($value);
        } elseif (is_int($value)) {
            return NumberValue::fromInteger($value);
        } elseif (is_float($value)) {
            return NumberValue::fromFloat($value);
        } elseif (is_bool($value)) {
            return BooleanValue::fromBoolean($value);
        } elseif (is_null($value)) {
            return NullValue::create();
        } elseif (is_array($value)) {
            return PhpArrayValue::fromArray($value);
        } elseif ($value instanceof \Closure) {
            return PhpClosureValue::fromClosure($value);
        } elseif (is_object($value)) {
            return PhpClassInstanceValue::fromObject($value);
        } else {
            throw new \RuntimeException('@TODO: Could not convert type: ' . gettype($value));
        }
    }
}