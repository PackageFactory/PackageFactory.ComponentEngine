<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime;

use PackageFactory\ComponentEngine\Runtime\Context\Value\ListValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\OperationValue;
use PackageFactory\ComponentEngine\Runtime\Context\Value\PhpArrayValue;
use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Library\OperationInterface;

final class Library
{
    /**
     * @var array<class-string, array<string, OperationInterface<mixed>>>
     */
    private $operations;

    /**
     * @param array<class-string, array<string, OperationInterface<mixed>>> $operations
     */
    private function __construct(array $operations)
    {
        $this->operations = $operations;
    }

    /**
     * @return self
     */
    public static function default(): self
    {
        return new self([
            ListValue::class => [
                'map' => Library\Operation\Map::create()
            ],
            PhpArrayValue::class => [
                'map' => Library\Operation\Map::create()
            ]
        ]);
    }

    /**
     * @param class-string $typeName
     * @param string $operationName
     * @return boolean
     */
    public function hasOperation(string $typeName, string $operationName): bool
    {
        return isset($this->operations[$typeName][$operationName]);
    }

    /**
     * @param class-string $typeName
     * @param string $operationName
     * @param ValueInterface<mixed> $value
     * @return OperationValue
     */
    public function getOperation(string $typeName, string $operationName, ValueInterface $value): OperationValue
    {
        if (!$this->hasOperation($typeName, $operationName)) {
            throw new \Exception('@TODO: Cannot retrieve undefined operation!');
        }

        return OperationValue::fromValueAndOperation(
            $value,
            $this->operations[$typeName][$operationName]
        );
    }

    /**
     * @param class-string $typeName
     * @param string $operationName
     * @param OperationInterface<mixed> $operation
     * @return self
     */
    public function withAddedOperation(string $typeName, string $operationName, OperationInterface $operation): self
    {
        if ($this->hasOperation($typeName, $operationName)) {
            throw new \Exception('@TODO: operation already exists!');
        }

        $operations = $this->operations;
        $operations[$typeName][$operationName] = $operation;

        return new self($operations);
    }

    /**
     * @param class-string $typeName
     * @param string $operationName
     * @param OperationInterface<mixed> $operation
     * @return self
     */
    public function withOverridenOperation(string $typeName, string $operationName, OperationInterface $operation): self
    {
        if (!$this->hasOperation($typeName, $operationName)) {
            throw new \Exception('@TODO: operation does not exist!');
        }

        $operations = $this->operations;
        $operations[$typeName][$operationName] = $operation;

        return new self($operations);
    }

    /**
     * @param class-string $typeName
     * @param string $operationName
     * @return self
     */
    public function withoutOperation(string $typeName, string $operationName): self
    {
        if ($this->hasOperation($typeName, $operationName)) {
            $operations = $this->operations;
            unset($operations[$typeName][$operationName]);
    
            return new self($operations);
        } else {
            return $this;
        }
    }
}