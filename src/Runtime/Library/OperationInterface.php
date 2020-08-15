<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Library;

use PackageFactory\ComponentEngine\Runtime\Context\Value\ListValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @template R
 */
interface OperationInterface
{
    /**
     * @param ValueInterface<mixed> $value
     * @param Runtime $runtime
     * @param ListValue $arguments
     * @return ValueInterface<R>
     */
    public function run(ValueInterface $value, Runtime $runtime, ListValue $arguments): ValueInterface;
}