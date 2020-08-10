<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context;

final class Value
{
    /**
     * @param mixed $value
     * @return ValueInterface
     */
    public static function fromAny($value): ValueInterface
    {
        if (is_null($value)) {
            return Value\NullValue::create();
        } elseif (is_array($value)) {
            return Value\ArrayValue::fromArray($value);
        } elseif (is_bool($value)) {
            return Value\BooleanValue::fromBoolean($value);
        } elseif ($value instanceof \Closure) {
            return Value\CallableValue::fromClosure($value);
        } elseif (is_float($value)) {
            return Value\NumberValue::fromFloat($value);
        } elseif (is_int($value)) {
            return Value\NumberValue::fromInteger($value);
        } elseif (is_object($value)) {
            return Value\ObjectValue::fromObject($value);
        } elseif (is_string($value)) {
            return Value\StringValue::fromString($value);
        } else {
            throw new \RuntimeException('@TODO: Unrecognized Value.');
        }
    }
}