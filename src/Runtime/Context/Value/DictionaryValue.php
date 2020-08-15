<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @extends Value<\stdClass>
 */
final class DictionaryValue extends Value
{
    /**
     * @var array<string, ValueInterface<mixed>>
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
     * @return self
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @param array<mixed> $array
     * @return self
     */
    public static function fromArray(array $array): self
    {
        return new self(
            array_map(
                function ($item) {
                    return $item instanceof ValueInterface ? $item : PhpValue::fromAny($item);
                },
                $array
            )
        );
    }

    /**
     * @param \Iterator<ValueInterface<mixed>> $iterator
     * @return self
     */
    public static function fromValueIterator(\Iterator $iterator): self
    {
        return new self(iterator_to_array($iterator, true));
    }

    /**
     * @return BooleanValue
     */
    public function asBooleanValue(): BooleanValue
    {
        return BooleanValue::fromBoolean(count($this->value) !== 0);
    }

    /**
     * @return iterable<string, ValueInterface<mixed>>
     */
    public function asIterable(): iterable
    {
        foreach ($this->value as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * @param Key $key
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface<mixed>
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        if ($key->isNumeric()) {
            throw new \RuntimeException('@TODO: Invalid property access: ' . $key);
        } elseif (array_key_exists($key->getValue(), $this->value)) {
            return $this->value[$key->getValue()];
        } else {
            return NullValue::create();
        }
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
     */
    public function merge(ValueInterface $other): ValueInterface
    {
        if ($other instanceof DictionaryValue) {
            return new self(array_replace($this->value, $other->getArray()));
        } else {
            return parent::merge($other);
        }
    }

    /**
     * @return \stdClass
     */
    public function getValue()
    {
        return (object) array_map(
            function (ValueInterface $item) {
                return $item->getValue();
            },
            $this->value
        );
    }

    /**
     * @return array<mixed>
     */
    public function getArray(): array
    {
        return $this->value;
    }

    /**
     * @param string $key
     * @param ValueInterface<mixed> $value
     * @return self
     */
    public function withAddedProperty(string $key, ValueInterface $value): self
    {
        $properties = $this->value;
        $properties[$key] = $value;

        return new self($properties);
    }
}