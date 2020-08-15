<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Runtime;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;

/**
 * @extends PhpValue<array<mixed>>
 */
final class PhpArrayValue extends PhpValue
{
    /**
     * @var array<mixed>
     */
    private $array;

    /**
     * @param array<mixed> $array
     */
    private function __construct(array $array)
    {
        $this->array = $array;
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
        return BooleanValue::fromBoolean(count($this->array) !== 0);
    }

    /**
     * @return iterable<mixed, ValueInterface<mixed>>
     */
    public function asIterable(): iterable
    {
        foreach ($this->array as $key => $value) {
            yield $key => PhpValue::fromAny($value);
        }
    }

    /**
     * @param Key $key
     * @param boolean $optional
     * @param Runtime $runtime
     * @return ValueInterface<mixed>
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        if (array_key_exists($key->getValue(), $this->array)) {
            return PhpValue::fromAny($this->array[$key->getValue()]);
        } elseif ($runtime->getLibrary()->hasOperation(self::class, (string) $key)) {
            return $runtime->getLibrary()->getOperation(self::class, (string) $key, $this);
        } else {
            return NullValue::create();
        }
    }

    /**
     * @return array<mixed>
     */
    public function getValue()
    {
        return $this->array;
    }
}