<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value;

/**
 * @implements ValueInterface<\Closure>
 */
final class PhpClosureValue extends Value
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @return \Closure
     */
    public function getValue()
    {
        return $this->closure;
    }
}