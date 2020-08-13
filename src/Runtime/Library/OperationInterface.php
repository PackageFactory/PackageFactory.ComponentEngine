<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Library;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

interface OperationInterface
{
    /**
     * @param ValueInterface $value
     * @param Runtime $runtime
     * @param array<mixed> $arguments
     * @return void
     */
    public function run(ValueInterface $value, Runtime $runtime, array $arguments): ValueInterface;
}