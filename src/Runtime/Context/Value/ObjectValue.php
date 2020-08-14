<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\ProtectedContextAwareInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @extends Value<object>
 */
final class ObjectValue extends Value
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
     * @return BooleanValue
     */
    public function asBooleanValue(): BooleanValue
    {
        return BooleanValue::true();
    }

    /**
     * @param Key $key
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface<mixed>
     */
    public function get(Key $key, bool $optional, Runtime $runtime): ValueInterface
    {
        if (isset($this->value->{ $key->getValue() })) {
            return Value::fromAny($this->value->{ $key->getValue() });
        } elseif (is_callable([$this->value, (string) $key])) {
            if ($this->value instanceof ProtectedContextAwareInterface && $this->value->allowsCallOfMethod((string) $key->getValue())) {
                return ArrowFunctionValue::fromObjectAndMember($this->value, (string) $key->getValue());
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
     * @param array<int, ValueInterface<mixed>> $arguments
     * @param bool $optional
     * @return ValueInterface<mixed>
     */
    public function call(array $arguments, bool $optional): ValueInterface
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
     * @return object
     */
    public function getValue()
    {
        return $this->value;
    }
}