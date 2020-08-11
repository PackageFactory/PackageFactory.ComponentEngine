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
            // @NOTE: This is not an exact method to distinguish numerical and associative
            //        PHP Arrays - but it is the one that is going to be used here for 
            //        performance reasons.
            if (array_key_exists(0, $value)) {
                return Value\ArrayValue::fromArray($value);
            } else {
                return Value\DictionaryValue::fromArray($value);
            }
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