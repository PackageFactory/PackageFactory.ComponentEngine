<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;

final class ArrayValue implements ValueInterface
{
    /**
     * @var array<mixed>
     */
    private $value;

    /**
     * @param array<mixed> $value
     */
    private function __construct(array $value)
    {
        $this->value = $value;
    }

    /**
     * @param array<mixed> $value
     * @return self
     */
    public static function fromArray(array $value): self
    {
        return new self($value);
    }

    /**
     * @param Key $key
     * @param bool $optional
     * @return ValueInterface
     */
    public function get(Key $key, bool $optional): ValueInterface
    {
        if ($key->isNumeric()) {
            return $this->value[$key->getValue()] ?? NullValue::create();
        } else {
            throw new \RuntimeException('@TODO: Invalid key');
        }
    }

    /**
     * @param array<int, ValueInterface> $arguments
     * @param bool $optional
     * @return ValueInterface
     */
    public function call(array $arguments, bool $optional): ValueInterface
    {
        throw new \RuntimeException('@TODO: Array cannot be called');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function greaterThan(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Array cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function lessThan(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Array cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function equals(ValueInterface $other): ValueInterface
    {
        return BooleanValue::fromBoolean($this->value === $other->getValue());
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function add(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Array cannot be added to');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function subtract(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Array cannot be subtracted from');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function multiply(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Array cannot be multiplied');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function divide(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Array cannot be divided');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function modulo(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Array does not allow modulo operation');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function and(ValueInterface $other): ValueInterface
    {
        if (count($this->value) === 0) {
            return BooleanValue::fromBoolean(false);
        } else {
            return $other;
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function or(ValueInterface $other): ValueInterface
    {
        if (count($this->value) === 0) {
            return $other;
        } else {
            return $this;
        }
    }

    /**
     * @return ValueInterface
     */
    public function not(): ValueInterface
    {
        return BooleanValue::fromBoolean(count($this->value) === 0);
    }

    /**
     * @return array<mixed>
     */
    public function getValue()
    {
        return $this->value;
    }
}