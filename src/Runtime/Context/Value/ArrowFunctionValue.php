<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Value;

/**
 * @implements ValueInterface<\Closure>
 */
final class ArrowFunctionValue extends Value
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
     * @param callable $callable
     * @return self
     */
    public static function fromCallable(callable $callable): self
    {
        return new self($callable);
    }

    /**
     * @param \Closure $closure
     * @return self
     */
    public static function fromClosure(\Closure $closure): self
    {
        return new self($closure);
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
     * @return \Closure
     */
    public function getValue()
    {
        return $this->value;
    }
}