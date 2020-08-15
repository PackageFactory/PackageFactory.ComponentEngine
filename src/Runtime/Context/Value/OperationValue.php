<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Context\Value;

use PackageFactory\ComponentEngine\Runtime\Context\Value;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Library\OperationInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

/**
 * @extends Value<void>
 */
final class OperationValue extends Value
{
    /**
     * @var ValueInterface<mixed>
     */
    private $value;

    /**
     * @var OperationInterface<mixed>
     */
    private $operation;

    /**
     * @param ValueInterface<mixed> $value
     * @param OperationInterface<mixed> $operation
     */
    private function __construct(ValueInterface $value, OperationInterface $operation)
    {
        $this->value = $value;
        $this->operation = $operation;
    }

    /**
     * @param ValueInterface<mixed> $value
     * @param OperationInterface<mixed> $operation
     * @return self
     */
    public static function fromValueAndOperation(ValueInterface $value, OperationInterface $operation): self
    {
        return new self($value, $operation);
    }

    /**
     * @param ListValue $arguments
     * @param bool $optional
     * @param Runtime $runtime
     * @return ValueInterface<mixed>
     */
    public function call(ListValue $arguments, bool $optional, Runtime $runtime): ValueInterface
    {
        return $this->operation->run($this->value, $runtime, $arguments);
    }
}