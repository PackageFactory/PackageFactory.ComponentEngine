<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\BooleanLiteral;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Value;

/**
 * @extends Value<bool>
 */
final class BooleanValue extends Value
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
    public static function true(): self
    {
        return new self(true);
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
     * @return BooleanValue
     */
    public function asBooleanValue(): BooleanValue
    {
        return $this;
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return BooleanValue
     */
    public function greaterThan(ValueInterface $other): BooleanValue
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            return BooleanValue::fromBoolean($this->value > $other->getValue());
        } else {
            return parent::greaterThan($other);
        }
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return BooleanValue
     */
    public function lessThan(ValueInterface $other): BooleanValue
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            return BooleanValue::fromBoolean($this->value < $other->getValue());
        } else {
            return parent::lessThan($other);
        }
    }

    /**
     * @param ValueInterface<mixed> $other
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
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
     */
    public function add(ValueInterface $other): ValueInterface
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            return NumberValue::fromFloat((float) ($this->value + $other->getValue()));
        } elseif ($other instanceof StringValue) {
            return StringValue::fromString(((string) (int) $this->value) . $other->getValue());
        } else {
            return parent::add($other);
        }
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
     */
    public function subtract(ValueInterface $other): ValueInterface
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            return NumberValue::fromFloat((float) ($this->value - $other->getValue()));
        } else {
            return parent::subtract($other);
        }
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
     */
    public function multiply(ValueInterface $other): ValueInterface
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            return NumberValue::fromFloat($this->value * $other->getValue());
        } else {
            return parent::multiply($other);
        }
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
     */
    public function divide(ValueInterface $other): ValueInterface
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            if (!$other->getValue()) {
                throw new \RuntimeException('@TODO: Division by zero');
            }

            return NumberValue::fromFloat($this->value / $other->getValue());
        } else {
            return parent::divide($other);
        }
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
     */
    public function modulo(ValueInterface $other): ValueInterface
    {
        if ($other instanceof BooleanValue || $other instanceof NumberValue) {
            if (!$other->getValue()) {
                throw new \RuntimeException('@TODO: Division by zero');
            }

            return NumberValue::fromFloat((float) ($this->value % $other->getValue()));
        } else {
            return parent::modulo($other);
        }
    }

    /**
     * @template T
     * @param ValueInterface<T> $other
     * @return BooleanValue|ValueInterface<T>
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
     * @template T
     * @param ValueInterface<T> $other
     * @return BooleanValue|ValueInterface<T>
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
     * @return BooleanValue
     */
    public function not(): BooleanValue
    {
        return new self(!$this->value);
    }

    /**
     * @return bool
     */
    public function getValue()
    {
        return $this->value;
    }
}