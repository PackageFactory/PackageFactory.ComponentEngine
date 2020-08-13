<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context;

use PackageFactory\ComponentEngine\Runtime\Context\Value\BooleanValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\StringValue;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @template V
 */
interface ValueInterface
{
    /**
     * @return bool
     */
    public function isCountable(): bool;

    /**
     * @return bool
     */
    public function isCastableToString(): bool;

    /**
     * @return StringValue
     */
    public function asStringValue(): StringValue;

    /**
     * @return BooleanValue
     */
    public function asBooleanValue(): BooleanValue;

    /**
     * @return iterable
     */
    public function asIterable(): iterable;

    /**
     * @param Key $key
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface;

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function merge(ValueInterface $other): ValueInterface;

    /**
     * @param array<int, ValueInterface> $arguments
     * @param bool $optional
     * @return ValueInterface
     */
    public function call(array $arguments, bool $optional): ValueInterface;

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function greaterThan(ValueInterface $other): BooleanValue;

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function lessThan(ValueInterface $other): BooleanValue;

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function equals(ValueInterface $other): BooleanValue;

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function add(ValueInterface $other): ValueInterface;

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function subtract(ValueInterface $other): ValueInterface;

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function multiply(ValueInterface $other): ValueInterface;

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function divide(ValueInterface $other): ValueInterface;

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function modulo(ValueInterface $other): ValueInterface;

    /**
     * @return V
     */
    public function getValue();
}