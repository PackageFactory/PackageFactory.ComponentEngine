<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Key;
use PackageFactory\ComponentEngine\Runtime\Context\Value;

final class CallableValue implements ValueInterface
{
    /**
     * @var callable
     */
    private $value;

    /**
     * @param callable $value
     */
    private function __construct(callable $value)
    {
        $this->value = $value;
    }

    /**
     * @param \Closure $value
     * @return self
     */
    public static function fromClosure(\Closure $value): self
    {
        return new self($value);
    }

    /**
     * @param object $object
     * @param string $member
     * @return self
     */
    public static function fromObjectAndMember($object, string $member): self
    {
        if (is_callable([$object, $member])) {
            return new self(function (...$arguments) use ($object, $member) {
                return $object->{ $member }(...$arguments);
            });
        } else {
            throw new \RuntimeException('@TODO: ' . get_class($object) . '->' . $member . '() is not callable.');
        }
    }

    /**
     * @param Key $key
     * @param boolean $optional
     * @return ValueInterface
     */
    public function get(Key $key, bool $optional): ValueInterface
    {
        throw new \RuntimeException('@TODO: Callable has no children');
    }

    /**
     * @param array<int, ValueInterface> $arguments
     * @param bool $optional
     * @return ValueInterface
     */
    public function call(array $arguments, bool $optional): ValueInterface
    {
        $function = $this->value;
        $result = $function(...array_map(
            function (ValueInterface $value) { return $value->getValue(); }, 
            $arguments
        ));

        return Value::fromAny($result);
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function greaterThan(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Callable cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function lessThan(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Callable cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function equals(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Callable cannot be compared');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function add(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Callable cannot be added to');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function subtract(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Callable cannot be subtracted from');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function multiply(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Callable cannot be multiplied');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function divide(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Callable cannot be divided');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function modulo(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Callable does not allow modulo operation');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function and(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Callable does not allow conjunction');
    }

    /**
     * @param ValueInterface $other
     * @return ValueInterface
     */
    public function or(ValueInterface $other): ValueInterface
    {
        throw new \RuntimeException('@TODO: Callable does not allow disjunction');
    }

    /**
     * @return ValueInterface
     */
    public function not(): ValueInterface
    {
        throw new \RuntimeException('@TODO: Callable does not allow negation');
    }

    /**
     * @return callable
     */
    public function getValue()
    {
        return $this->value;
    }
}