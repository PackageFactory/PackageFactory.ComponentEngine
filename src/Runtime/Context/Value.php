<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context;

use PackageFactory\ComponentEngine\Runtime\Context\Value\BooleanValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\StringValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\VirtualDOM\Model\ComponentInterface;

abstract class Value implements ValueInterface
{
    /**
     * @return bool
     */
    public function isCountable(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isCastableToString(): bool
    {
        return false;
    }

    /**
     * @return StringValue
     */
    public function asStringValue(): StringValue
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: %s is not castable to string.',
                static::class
            )
        );
    }

    /**
     * @return BooleanValue
     */
    public function asBooleanValue(): BooleanValue
    {
        return BooleanValue::true();
    }

    /**
     * @return iterable
     */
    public function asIterable(): iterable
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: %s is not iterable.',
                static::class
            )
        );
    }

    /**
     * @param mixed $value
     * @return ValueInterface
     */
    public static function fromAny($value): ValueInterface
    {
        if ($value instanceof ValueInterface) {
            return $value;
        } elseif (is_null($value)) {
            return Value\NullValue::create();
        } elseif (is_array($value)) {
            // @NOTE: This is not an exact method to distinguish numerical and associative
            //        PHP Arrays - but it is the one that is going to be used here for 
            //        performance reasons.
            if (array_key_exists(0, $value)) {
                return Value\ListValue::fromArray($value);
            } else {
                return Value\DictionaryValue::fromArray($value);
            }
        } elseif (is_bool($value)) {
            return Value\BooleanValue::fromBoolean($value);
        } elseif ($value instanceof \Closure) {
            return Value\ArrowFunctionValue::fromClosure($value);
        } elseif ($value instanceof ComponentInterface) {
            return Value\AfxValue::fromComponent($value);
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

    /**
     * @param Key $key
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: Cannot get %s from %s.',
                $key,
                static::class
            )
        );
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function merge(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: Cannot merge %s with %s.',
                static::class,
                get_class($other)
            )
        );
    }

    /**
     * @param array<int, ValueInterface> $arguments
     * @param bool $optional
     * @return ValueInterface
     */
    public function call(array $arguments, bool $optional): ValueInterface
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: Cannot call %s',
                static::class
            )
        );
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function greaterThan(ValueInterface $other): BooleanValue
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: Cannot compare %s with %s',
                static::class,
                get_class($other)
            )
        );
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function lessThan(ValueInterface $other): BooleanValue
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: Cannot compare %s with %s',
                static::class,
                get_class($other)
            )
        );
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function equals(ValueInterface $other): BooleanValue
    {
        return BooleanValue::fromBoolean(
            $this->getValue() === $other->getValue()
        );
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function add(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: Addition of %s is not permitted on %s',
                get_class($other),
                static::class
            )
        );
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function subtract(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: Subtraction of %s is not permitted on %s',
                get_class($other),
                static::class
            )
        );
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function multiply(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: Multiplication of %s is not permitted on %s',
                get_class($other),
                static::class
            )
        );
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function divide(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: Division of %s is not permitted on %s',
                get_class($other),
                static::class
            )
        );
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function modulo(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: Modulo of %s is not permitted on %s',
                get_class($other),
                static::class
            )
        );
    }

    /**
     * @return mixed
     */
    abstract public function getValue();
}