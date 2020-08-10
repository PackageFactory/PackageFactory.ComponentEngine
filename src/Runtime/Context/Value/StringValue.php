<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;

final class StringValue implements ValueInterface
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param string $value
     * @return self
     */
    public static function fromString(string $value): self
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
            return StringValue::fromString($this->value[$key->getValue()]) ?? NullValue::create();
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
        throw new \RuntimeException('@TODO: String cannot be called');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function greaterThan(ValueInterface $other): ValueInterface
    {
        if ($other instanceof StringValue) {
            return BooleanValue::fromBoolean($this->value > $other->getValue());
        } else {
            throw new \RuntimeException('@TODO: String cannot be compared with ' . get_class($other));
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function lessThan(ValueInterface $other): ValueInterface
    {
        if ($other instanceof StringValue) {
            return BooleanValue::fromBoolean($this->value < $other->getValue());
        } else {
            throw new \RuntimeException('@TODO: String cannot be compared with ' . get_class($other));
        }
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
        if ($other instanceof StringValue || $other instanceof BooleanValue || $other instanceof NumberValue) {
            return StringValue::fromString($this->value . ((string) $other->getValue()));
        } else {
            throw new \RuntimeException('@TODO: String cannot be compared with ' . get_class($other));
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function subtract(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: String cannot be subtracted from');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function multiply(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: String cannot be multiplied');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function divide(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: String cannot be divided');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function modulo(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: String does not allow modulo operation');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function and(ValueInterface $other): ValueInterface
    {
        if ($this->value === '') {
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
        if ($this->value === '') {
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
        return BooleanValue::fromBoolean($this->value === '');
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}