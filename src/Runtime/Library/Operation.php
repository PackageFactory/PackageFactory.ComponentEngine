<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Library;

use PackageFactory\ComponentEngine\Runtime\Context\Value\ListValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @template R
 * @implements OperationInterface<R>
 */
abstract class Operation implements OperationInterface
{
    /**
     * Private constructor
     */
    final protected function __construct()
    {
    }

    /**
     * @param ValueInterface<mixed> $value
     * @param Runtime $runtime
     * @param ListValue $arguments
     * @return ValueInterface<R>
     */
    abstract public function run(ValueInterface $value, Runtime $runtime, ListValue $arguments): ValueInterface;
}