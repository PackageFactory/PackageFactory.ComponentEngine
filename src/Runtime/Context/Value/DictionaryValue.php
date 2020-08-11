<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value;

final class DictionaryValue implements ValueInterface
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
     * @param array<mixed> $array
     * @return self
     */
    public static function fromArray(array $array): self
    {
        return new self($array);
    }

    /**
     * @param Key $key
     * @param bool $optional
     * @return ValueInterface
     */
    public function get(Key $key, bool $optional): ValueInterface
    {
        if (array_key_exists($key->getValue(), $this->value)) {
            return Value::fromAny($this->value[$key->getValue()]);
        } elseif ($optional) {
            return NullValue::create();
        } else {
            throw new \RuntimeException('@TODO: Invalid property access');
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function merge(ValueInterface $other): ValueInterface
    {
        if ($other instanceof DictionaryValue) {
            return new self(array_replace($this->value, $other->getArray()));
        } else {
            throw new \RuntimeException('@TODO: Dictionary cannot be merged with ' . get_class($other));
        }
    }

    /**
     * @param array<int, ValueInterface> $arguments
     * @param bool $optional
     * @return ValueInterface
     */
    public function call(array $arguments, bool $optional): ValueInterface
    {
        throw new \RuntimeException('@TODO: Dictionary cannot be called');
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function greaterThan(ValueInterface $other): BooleanValue
    {
        throw new \RuntimeException('@TODO: Dictionary cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function lessThan(ValueInterface $other): BooleanValue
    {
        throw new \RuntimeException('@TODO: Dictionary cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function equals(ValueInterface $other): BooleanValue
    {
        return BooleanValue::fromBoolean($this->value === $other->getValue());
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function add(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Dictionary cannot be added to');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function subtract(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Dictionary cannot be subtracted from');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function multiply(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Dictionary cannot be multiplied');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function divide(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Dictionary cannot be divided');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function modulo(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Dictionary does not allow modulo operation');
    }

    /**
     * @return bool
     */
    public function isTrueish(): bool
    {
        return count($this->value) !== 0;
    }

    /**
     * @return object
     */
    public function getValue()
    {
        return (object) $this->value;
    }

    /**
     * @return array<mixed>
     */
    public function getArray(): array
    {
        return $this->value;
    }
}