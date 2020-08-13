<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\NumberLiteral;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @implements ValueInterface<float>
 */
final class NumberValue extends Value
{
    /**
     * @var float
     */
    private $value;

    /**
     * @param float $value
     */
    private function __construct(float $value)
    {
        $this->value = $value;
    }

    /**
     * @return self
     */
    public static function zero(): self
    {
        return new self(0.0);
    }

    /**
     * @param float $value
     * @return self
     */
    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    /**
     * @param int $value
     * @return self
     */
    public static function fromInteger(int $value): self
    {
        return new self((float) $value);
    }

    /**
     * @param NumberLiteral $numberLiteral
     * @return self
     */
    public static function fromNumberLiteral(NumberLiteral $numberLiteral): self
    {
        return new self($numberLiteral->getNumber());
    }

    /**
     * @return BooleanValue
     */
    public function asBooleanValue(): BooleanValue
    {
        return BooleanValue::fromBoolean($this->value !== 0.0);
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
            return parent::greaterThan($other);
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
            return parent::lessThan($other);
        }
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function equals(ValueInterface $other): BooleanValue
    {
        if ($other instanceof NumberValue) {
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
            return StringValue::fromString(((string) $this->value) . $other->getValue());
        } else {
            return parent::add($other);
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
            return parent::subtract($other);
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
            return parent::multiply($other);
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
            return parent::divide($other);
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
            return parent::modulo($other);
        }
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }
}