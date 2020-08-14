<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Value;

/**
 * @extends Value<\Closure>
 */
final class ArrowFunctionValue extends Value
{
    /**
     * @var \Closure
     */
    private $value;

    /**
     * @param \Closure $value
     */
    private function __construct(\Closure $value)
    {
        $this->value = $value;
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
     * @param array<int, ValueInterface<mixed>> $arguments
     * @param bool $optional
     * @return ValueInterface<mixed>
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