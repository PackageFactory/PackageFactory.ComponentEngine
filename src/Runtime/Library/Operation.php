<?php declare(strict_types=1);
namespace PackageFactory\ComponentEngine\Runtime\Library;

use PackageFactory\ComponentEngine\Runtime\Context\ValueInterface;
use PackageFactory\ComponentEngine\Runtime\Runtime;

abstract class Operation implements OperationInterface
{
    /**
     * @var array<string, OperationInterface>
     */
    private static $instances = [];

    /**
     * Private constructor
     */
    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function create(): self
    {
        if (isset(self::$instances[static::class])) {
            return self::$instances[static::class];
        }

        return self::$instances[static::class] = new static();
    }

    /**
     * @param ValueInterface $value
     * @param Runtime $runtime
     * @param array<mixed> $arguments
     * @return void
     */
    abstract public function run(ValueInterface $value, Runtime $runtime, array $arguments): ValueInterface;
}