<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Library;

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
     * @param array<mixed> $arguments
     * @return ValueInterface<R>
     */
    public function run(ValueInterface $value, Runtime $runtime, array $arguments): ValueInterface;
}