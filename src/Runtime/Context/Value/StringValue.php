<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Parser\Ast\Expression\StringLiteral;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @extends Value<string>
 */
final class StringValue extends Value
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
     * @return boolean
     */
    public function isCastableToString(): bool
    {
        return true;
    }

    /**
     * @return StringValue
     */
    public function asStringValue(): StringValue
    {
        return $this;
    }

    /**
     * @return BooleanValue
     */
    public function asBooleanValue(): BooleanValue
    {
        return BooleanValue::fromBoolean($this->value !== '');
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
            return StringValue::fromString($this->value[$key->getValue()]) ?? NullValue::create();
        } else {
            return parent::get($key, $optional, $runtime);
        }
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return BooleanValue
     */
    public function greaterThan(ValueInterface $other): BooleanValue
    {
        if ($other instanceof StringValue) {
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
        if ($other instanceof StringValue) {
            return BooleanValue::fromBoolean($this->value < $other->getValue());
        } else {
            return parent::lessThan($other);
        }
    }

    /**
     * @param ValueInterface<mixed> $other
     * @return ValueInterface<mixed>
     */
    public function add(ValueInterface $other): ValueInterface
    {
        if ($other instanceof StringValue || $other instanceof BooleanValue || $other instanceof NumberValue) {
            return StringValue::fromString($this->value . ((string) $other->getValue()));
        } else {
            return parent::add($other);
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}