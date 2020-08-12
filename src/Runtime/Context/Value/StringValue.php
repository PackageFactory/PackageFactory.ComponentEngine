<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\StringLiteral;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Runtime;

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
     * @param StringLiteral $value
     * @return self
     */
    public static function fromStringLiteral(StringLiteral $value): self
    {
        return new self($value->getValue());
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
            return StringValue::fromString($this->value[$key->getValue()]) ?? NullValue::create();
        } else {
            throw new \RuntimeException('@TODO: Invalid key');
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function merge(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: String cannot be merged with ' . get_class($other));
    }

    /**
     * @param array<int, ValueInterface> $arguments
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface
     */
    public function call(array $arguments, bool $optional, Runtime $runtime): ValueInterface
    {
        throw new \RuntimeException('@TODO: String cannot be called');
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function greaterThan(ValueInterface $other): BooleanValue
    {
        if ($other instanceof StringValue) {
            return BooleanValue::fromBoolean($this->value > $other->getValue());
        } else {
            throw new \RuntimeException('@TODO: String cannot be compared with ' . get_class($other));
        }
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function lessThan(ValueInterface $other): BooleanValue
    {
        if ($other instanceof StringValue) {
            return BooleanValue::fromBoolean($this->value < $other->getValue());
        } else {
            throw new \RuntimeException('@TODO: String cannot be compared with ' . get_class($other));
        }
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
     * @return bool
     */
    public function isTrueish(): bool
    {
        return $this->value !== '';
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}