<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Library\OperationInterface;

/**
 * @extends Value<OperationInterface<mixed>>
 */
final class OperationValue extends Value
{
    /**
     * @var OperationInterface<mixed>
     */
    private $operation;

    /**
     * @param OperationInterface<mixed> $operation
     */
    private function __construct(OperationInterface $operation)
    {
        $this->operation = $operation;
    }

    /**
     * @param OperationInterface<mixed> $operation
     * @return self
     */
    public static function fromOperation(OperationInterface $operation): self
    {
        return new self($operation);
    }

    /**
     * @return OperationInterface<mixed>
     */
    public function getValue()
    {
        return $this->operation;
    }
}