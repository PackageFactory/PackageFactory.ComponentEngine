<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\BooleanLiteral;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class BooleanValue implements ValueInterface
{
    /**
     * @var bool
     */
    private $value;

    /**
     * @param bool $value
     */
    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    /**
     * @param bool $value
     * @return self
     */
    public static function fromBoolean(bool $value): self
    {
        return new self($value);
    }

    /**
     * @return self
     */
    public static function false(): self
    {
        return new self(false);
    }

    /**
     * @param BooleanLiteral $booleanLiteral
     * @return self
     */
    public static function fromBooleanLiteral(BooleanLiteral $booleanLiteral): self
    {
        return new self($booleanLiteral->getBoolean());
    }

    /**
     * @param Key $key
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        throw new \RuntimeException('@TODO: Boolean has no children');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function merge(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Boolean cannot be merged with ' . get_class($other));
    }

    /**
     * @param array<int, ValueInterface> $arguments
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface
     */
    public function call(array $arguments, bool $optional, Runtime $runtime): ValueInterface
    {
        throw new \RuntimeException('@TODO: Boolean cannot be called');
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function greaterThan(ValueInterface $other): BooleanValue
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            return BooleanValue::fromBoolean($this->value > $other->getValue());
        } else {
            throw new \RuntimeException('@TODO: Boolean cannot be compared with ' . get_class($other));
        }
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function lessThan(ValueInterface $other): BooleanValue
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            return BooleanValue::fromBoolean($this->value < $other->getValue());
        } else {
            throw new \RuntimeException('@TODO: Boolean cannot be compared with ' . get_class($other));
        }
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function equals(ValueInterface $other): BooleanValue
    {
        if ($other instanceof BooleanValue) {
            return BooleanValue::fromBoolean($this->value === $other->getValue());
        } else {
            return BooleanValue::fromBoolean(false);
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function add(ValueInterface $other): ValueInterface
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            return NumberValue::fromFloat((float) ($this->value + $other->getValue()));
        } elseif ($other instanceof StringValue) {
            return StringValue::fromString(((string) (int) $this->value) . $other->getValue());
        } else {
            throw new \RuntimeException('@TODO: ' . get_class($other) . ' cannot be added to Boolean');
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function subtract(ValueInterface $other): ValueInterface
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            return NumberValue::fromFloat((float) ($this->value - $other->getValue()));
        } else {
            throw new \RuntimeException('@TODO: ' . get_class($other) . ' cannot be subtracted from Boolean');
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function multiply(ValueInterface $other): ValueInterface
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            return NumberValue::fromFloat($this->value * $other->getValue());
        } else {
            throw new \RuntimeException('@TODO: Boolean cannot be multiplied with ' . get_class($other));
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function divide(ValueInterface $other): ValueInterface
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            if (!$other->getValue()) {
                throw new \RuntimeException('@TODO: Division by zero');
            }

            return NumberValue::fromFloat($this->value / $other->getValue());
        } else {
            throw new \RuntimeException('@TODO: Boolean cannot be divided by ' . get_class($other));
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function modulo(ValueInterface $other): ValueInterface
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            if (!$other->getValue()) {
                throw new \RuntimeException('@TODO: Division by zero');
            }

            return NumberValue::fromFloat((float) ($this->value % $other->getValue()));
        } else {
            throw new \RuntimeException('@TODO: Modulo operation is not allowed between Boolean and ' . get_class($other));
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function and(ValueInterface $other): ValueInterface
    {
        if ($this->value === true) {
            return $other;
        } else {
            return $this;
        }
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function or(ValueInterface $other): ValueInterface
    {
        if ($this->value === false) {
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
        return new self(!$this->value);
    }

    /**
     * @return bool
     */
    public function isTrueish(): bool
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function getValue()
    {
        return $this->value;
    }
}