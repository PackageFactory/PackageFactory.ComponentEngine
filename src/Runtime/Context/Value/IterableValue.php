<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class IterableValue implements ValueInterface
{
    /**
     * @var iterable<mixed>
     */
    private $value;

    /**
     * @param iterable<mixed> $value
     */
    private function __construct(iterable $value)
    {
        $this->value = $value;
    }

    /**
     * @param \Iterator<mixed> $iterator
     * @return self
     */
    public static function fromIterator(\Iterator $iterator): self
    {
        return new self($iterator);
    }

    /**
     * @param Key $key
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        if ($runtime->getLibrary()->hasMethod('iterable', (string) $key->getValue())) {
            return $runtime->getLibrary()->getMethod('iterable', (string) $key->getValue(), $this);
        } elseif ($this->value instanceof \Iterator) {
            foreach ($this->value as $k => $v) {
                if (is_string($k)) {
                    return DictionaryValue::fromArray(iterator_to_array($this->value, true))
                        ->get($key, $optional, $runtime);
                } else {
                    return ArrayValue::fromArray(iterator_to_array($this->value, true))
                        ->get($key, $optional, $runtime);
                }
            }
        }

        return Value::fromAny($this->value)->get($key, $optional, $runtime);
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function merge(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Iterable Value cannot be merged with ' . get_class($other));
    }

    /**
     * @param array<int, ValueInterface> $arguments
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface
     */
    public function call(array $arguments, bool $optional, Runtime $runtime): ValueInterface
    {
        throw new \RuntimeException('@TODO: Iterable Value cannot be called');
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function greaterThan(ValueInterface $other): BooleanValue
    {
        throw new \RuntimeException('@TODO: Iterable Value cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function lessThan(ValueInterface $other): BooleanValue
    {
        throw new \RuntimeException('@TODO: Iterable Value cannot be compared');
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
        throw new \RuntimeException('@TODO: Iterable Value cannot be added to');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function subtract(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Iterable Value cannot be subtracted from');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function multiply(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Iterable Value cannot be multiplied');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function divide(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Iterable Value cannot be divided');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function modulo(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Iterable Value does not allow modulo operation');
    }

    /**
     * @return bool
     */
    public function isTrueish(): bool
    {
        if (is_countable($this->value)) {
            return count($this->value) !== 0;
        } else {
            return true;
        }
    }

    /**
     * @return iterable<mixed>
     */
    public function getValue()
    {
        return $this->value;
    }
}