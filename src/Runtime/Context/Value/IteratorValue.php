<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @implements ValueInterface<\Iterator<mixed>>
 */
final class IteratorValue extends Value
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
     * @return BooleanValue
     */
    public function asBooleanValue(): BooleanValue
    {
        if (is_countable($this->value)) {
            return BooleanValue::fromBoolean(count($this->value) !== 0);
        } else {
            return BooleanValue::true();
        }
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
                    return ListValue::fromArray(iterator_to_array($this->value, true))
                        ->get($key, $optional, $runtime);
                }
            }
        }

        return Value::fromAny($this->value)->get($key, $optional, $runtime);
    }

    /**
     * @return \Iterator<mixed>
     */
    public function getValue()
    {
        return $this->value;
    }
}