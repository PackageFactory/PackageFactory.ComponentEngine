<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;

final class NullValue implements ValueInterface
{
    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param Key $key
     * @param bool $optional
     * @return ValueInterface
     */
    public function get(Key $key, bool $optional): ValueInterface
    {
        if ($optional) {
            return $this;
        } else {
            throw new \RuntimeException('@TODO: Illegal access');
        }
    }

    /**
     * @param array<int, ValueInterface> $arguments
     * @param bool $optional
     * @return ValueInterface
     */
    public function call(array $arguments, bool $optional): ValueInterface
    {
        if ($optional) {
            return $this;
        } else {
            throw new \RuntimeException('@TODO: Illegal call');
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function greaterThan(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Null cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function lessThan(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Null cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function equals(ValueInterface $other): ValueInterface
    {
        return BooleanValue::fromBoolean($other instanceof NullValue);
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function add(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Null cannot be added to');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function subtract(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Null cannot be subtracted from');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function multiply(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Null cannot be multiplied');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function divide(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Null cannot be divided');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function modulo(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Null does not allow modulo operation');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function and(ValueInterface $other): ValueInterface
    {
        return BooleanValue::fromBoolean(false);
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function or(ValueInterface $other): ValueInterface
    {
        return $other;
    }

    /**
     * @return ValueInterface
     */
    public function not(): ValueInterface
    {
        return BooleanValue::fromBoolean(true);
    }

    /**
     * @return null
     */
    public function getValue()
    {
        return null;
    }
}