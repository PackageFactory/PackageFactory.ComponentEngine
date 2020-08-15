<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context;

use PackageFactory\ComponentEngine\Runtime\Context\Value\BooleanValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\ListValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\StringValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\VirtualDOM\Model\ComponentInterface;

/**
 * @template V
 * @implements ValueInterface<V>
 */
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
     * @return iterable<mixed>
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
     * @param Key $key
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface<mixed>
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        if ($runtime->getLibrary()->hasOperation(static::class, (string) $key)) {
            return $runtime->getLibrary()->getOperation(static::class, (string) $key, $this);
        } else {
            throw new \RuntimeException(
                sprintf(
                    '@TODO: Cannot get %s from %s.',
                    $key,
                    static::class
                )
            );
        }
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
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
     * @param ListValue $arguments
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface<mixed>
     */
    public function call(ListValue $arguments, bool $optional, Runtime $runtime): ValueInterface
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: Cannot call %s',
                static::class
            )
        );
    }

    /**
     * @param ValueInterface<mixed> $other
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
     * @param ValueInterface<mixed> $other
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
     * @param ValueInterface<mixed> $other
     * @return BooleanValue
     */
    public function equals(ValueInterface $other): BooleanValue
    {
        return BooleanValue::fromBoolean(
            $this->getValue() === $other->getValue()
        );
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
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
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
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
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
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
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
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
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
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
     * @return V
     */
    public function getValue()
    {
        throw new \RuntimeException(
            sprintf(
                '@TODO: Cannot get value of %s',
                static::class
            )
        );
    }

    /**
     * @return string
     */
    public function getDebugName(): string
    {
        return basename(static::class);
    }
}