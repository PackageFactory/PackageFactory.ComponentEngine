<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

/**
 * @extends PhpValue<\Closure>
 */
final class PhpClosureValue extends PhpValue
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @param \Closure $closure
     */
    private function __construct(\Closure $closure)
    {
        $this->closure = $closure;
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
     * @return \Closure
     */
    public function getValue()
    {
        return $this->closure;
    }
}