<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @implements ValueInterface<array<int, mixed>>
 */
final class ListValue extends Value
{
    /**
     * @var array<int, ValueInterface>
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
        if ($key->isNumeric()) {
            if (array_key_exists($key->getValue(), $this->value)) {
                return Value::fromAny($this->value[$key->getValue()]);
            } elseif ($optional) {
                return NullValue::create();
            } else {
                throw new \RuntimeException('@TODO: Invalid property access');
            }
        } elseif ($runtime->getLibrary()->hasMethod('array', (string) $key->getValue())) {
            return $runtime->getLibrary()->getMethod('array', (string) $key->getValue(), $this);
        } else {
            throw new \RuntimeException('@TODO: Invalid key');
        }
    }

    /**
     * @return array<int, mixed>
     */
    public function getValue()
    {
        return $this->value;
    }
}