<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @extends Value<array<int, mixed>>
 */
final class ListValue extends Value
{
    /**
     * @var array<int, ValueInterface<mixed>>
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
     * @return BooleanValue
     */
    public function asBooleanValue(): BooleanValue
    {
        return BooleanValue::fromBoolean(count($this->value) !== 0);
    }

    /**
     * @return iterable<mixed>
     */
    public function asIterable(): iterable
    {
        return $this->value;
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
            if (array_key_exists($key->getValue(), $this->value)) {
                return $this->value[$key->getValue()];
            } else {
                return NullValue::create();
            }
        } else {
            throw new \RuntimeException('@TODO: Invalid key');
        }
    }

    /**
     * @return array<int, mixed>
     */
    public function getValue()
    {
        return array_map(
            function (ValueInterface $item) {
                return $item->getValue();
            },
            $this->value
        );
    }

    /**
     * @param ValueInterface<mixed> $item
     * @return self
     */
    public function withAddedItem(ValueInterface $item): self
    {
        $items = $this->value;
        $items[] = $item;

        return new self($items);
    }
}