<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\ProtectedContextAwareInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Runtime;

final class ObjectValue implements ValueInterface
{
    /**
     * @var object
     */
    private $value;

    /**
     * @param object $value
     */
    private function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param object $value
     * @return self
     */
    public static function fromObject($value): self
    {
        return new self($value);
    }

    /**
     * @param Key $key
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        if (isset($this->value->{ $key->getValue() })) {
            return Value::fromAny($this->value->{ $key->getValue() });
        } elseif (is_callable([$this->value, (string) $key])) {
            if ($this->value instanceof ProtectedContextAwareInterface && $this->value->allowsCallOfMethod((string) $key->getValue())) {
                return CallableValue::fromObjectAndMember($this->value, (string) $key->getValue());
            } else {
                throw new \RuntimeException('@TODO: Call to ' . get_class($this->value) . '->' . $key->getValue() . '() is not allowed.');
            }
        } else {
            $getter = $key->asGetter();

            if (is_callable([$this->value, $getter])) {
                try {
                    return Value::fromAny($this->value->{ $getter }());
                } catch (\Throwable $err) {
                    throw new \RuntimeException('@TODO: An error occured during PHP execution: ' . $err->getMessage());
                }
            }
        }

        return NullValue::create();
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function merge(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Object cannot be merged with ' . get_class($other));
    }

    /**
     * @param array<int, ValueInterface> $arguments
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface
     */
    public function call(array $arguments, bool $optional, Runtime $runtime): ValueInterface
    {
        if (is_callable([$this->value, '__invoke'], true) && $this->value instanceof ProtectedContextAwareInterface && $this->value->allowsCallOfMethod('__invoke')) {
            $function = $this->value;
            $result = $function(...array_map(
                function (ValueInterface $value) { return $value->getValue(); }, 
                $arguments
            ));

            return Value::fromAny($result);
        } else {
            throw new \RuntimeException('@TODO: Object cannot be called');
        }
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function greaterThan(ValueInterface $other): BooleanValue
    {
        throw new \RuntimeException('@TODO: Object cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return BooleanValue
     */
    public function lessThan(ValueInterface $other): BooleanValue
    {
        throw new \RuntimeException('@TODO: Object cannot be compared');
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
        throw new \RuntimeException('@TODO: Object cannot be added to');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function subtract(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Object cannot be subtracted from');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function multiply(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Object cannot be multiplied');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function divide(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Object cannot be divided');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function modulo(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Object does not allow modulo operation');
    }

    /**
     * @return bool
     */
    public function isTrueish(): bool
    {
        return true;
    }

    /**
     * @return object
     */
    public function getValue()
    {
        return $this->value;
    }
}