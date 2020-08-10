<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context;

interface ValueInterface
{
    /**
     * @param Key $key
     * @param bool $optional
     * @return ValueInterface
     */
    public function get(Key $key, bool $optional): ValueInterface;

    /**
     * @param array<int, ValueInterface> $arguments
     * @param bool $optional
     * @return ValueInterface
     */
    public function call(array $arguments, bool $optional): ValueInterface;

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function greaterThan(ValueInterface $other): ValueInterface;

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function lessThan(ValueInterface $other): ValueInterface;

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function equals(ValueInterface $other): ValueInterface;

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
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function and(ValueInterface $other): ValueInterface;

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function or(ValueInterface $other): ValueInterface;

    /**
     * @return ValueInterface
     */
    public function not(): ValueInterface;

    /**
     * @return mixed
     */
    public function getValue();
}