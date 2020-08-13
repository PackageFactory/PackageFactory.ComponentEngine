<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Library\OperationInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @implements ValueInterface<OperationInterface>
 */
final class OperationValue extends Value
{
    /**
     * @var OperationInterface
     */
    private $operation;

    /**
     * @return OperationInterface
     */
    public function getValue()
    {
        return $this->operation;
    }
}