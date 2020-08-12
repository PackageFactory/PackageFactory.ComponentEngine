<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context;

use PackageFactory\ComponentEngine\Runtime\Runtime;

final class RuntimeAwareClosure
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
    public function resolve(Runtime $runtime): \Closure
    {
        $closure = $this->closure;
        /** @var \Closure $value */
        $value = $closure($runtime);

        return $value;
    }

    /**
     * @return void
     */
    public function __invoke()
    {
        throw new \RuntimeException('@TODO: Illegal invokation of RuntimeAwareClosure.');
    }
}