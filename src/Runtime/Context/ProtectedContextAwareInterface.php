<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context;

interface ProtectedContextAwareInterface
{
    /**
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod(string $methodName): bool;
}