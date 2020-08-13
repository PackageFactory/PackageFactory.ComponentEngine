<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @implements ValueInterface<\stdClass>
 */
final class DictionaryValue extends Value
{
    /**
     * @var array<string, ValueInterface>
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
     * @return BooleanValue
     */
    public function asBooleanValue(): BooleanValue
    {
        return BooleanValue::fromBoolean(count($this->value) !== 0);
    }

    /**
     * @param Key $key
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        if (array_key_exists($key->getValue(), $this->value)) {
            return Value::fromAny($this->value[$key->getValue()]);
        } elseif ($optional) {
            return NullValue::create();
        } else {
            throw new \RuntimeException('@TODO: Invalid property access: ' . $key);
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
            return parent::merge($other);
        }
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