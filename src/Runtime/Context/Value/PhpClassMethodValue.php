<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value;

/**
 * @implements ValueInterface<callable>
 */
final class PhpClassMethodValue extends Value
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @return callable
     */
    public function getValue()
    {
        return [$this->className, $this->methodName];
    }
}