<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Runtime;

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
     * @param ListValue $arguments
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface<mixed>
     */
    public function call(ListValue $arguments, bool $optional, Runtime $runtime): ValueInterface
    {
        $function = $this->value;
        return $function($arguments);
    }

    /**
     * @return \Closure
     */
    public function getValue()
    {
        return $this->value;
    }
}